<#

.SYNOPSIS
	Temperature Update Handler

.DESCRIPTION
	Show temperature as measured by TEMPerHUM/TEMPerX USB devices - Handler Script

.FUNCTIONALITY
	Handler script that inserts temperature data into database.
	
	Run once per hour from windows task scheduler.

.NOTES
	Two timezone settings are required because the weather API uses PHP format and the handler uses Powershell format for database insert.

	See Function SendSMS below - delete custom function and enable generic one!! Twilio account required!
	
	Depending on your device, you may have to change the way Powershell reads the CSV output. 

.EXAMPLE
	Depending on your device, you may have to change the way Powershell reads the CSV output. 
	
	CSV output is comma separated. For temperature only models, the temperature comes second as so:
	$AtticTemp = [math]::Round(((Get-Content -Path $AtticTempCSV | Select -Last 1).Split(",")[1] -Replace "[^0-9-.]",""),1)
	
	Note "Split(",")[1]" which is the temperature reading in the CSV - humidity or outside temperature could be a different iteration. Change the iterator if that's the case.

#>

<#  Include required files  #>
Try {
	.("$PSScriptRoot\getCurrentTempConfig.ps1")
}
Catch {
	Write-Output "$(Get-Date -f G) : ERROR : Unable to load supporting PowerShell Scripts : $query `n$($Error[0])" | Out-File "$PSScriptRoot\PSError.log" -Append
}

<#  Function to send SMS via Twilio  #>
<#  Delete this custom function!!  #>
Function SendSMS($Num, $Msg) {
	& C:\scripts\Twilio\TwilioSend.ps1 -Num $Num -Msg $Msg
}

<#  Function to send SMS via Twilio  #>
<#  Use this function to send SMS via Twilio - Account Required! #>
<# 
Function SendSMS($Num, $Msg) {
	Try {
		$URL = "https://api.twilio.com/2010-04-01/Accounts/" + $SID + "/Messages.json"
		$Params = @{ To = $Num; From = $SMSFrom; Body = $Msg }
		$TokenSecureString = $Token | ConvertTo-SecureString -asPlainText -Force
		$Credential = New-Object System.Management.Automation.PSCredential($SID, $TokenSecureString)
		$SentMsg = Invoke-WebRequest $URL -Method Post -Credential $Credential -Body $Params -UseBasicParsing | ConvertFrom-Json
	}
	Catch {
		Write-Output "$(Get-Date -f G) : Error : $($Error[0])" | Out-File "$PSScriptRoot\SendError.log" -Append -Encoding ASCII
	}
}
#>

Function SendEmail ($Email, $Msg){
	Try {
		$Message = New-Object System.Net.Mail.Mailmessage $EmailFrom, $Email, $Subject, $Msg
		$Message.IsBodyHTML = $False
		$SMTP = New-Object System.Net.Mail.SMTPClient $SMTPServer,$SMTPPort
		$SMTP.EnableSsl = $SSL
		$SMTP.Credentials = New-Object System.Net.NetworkCredential($SMTPAuthUser, $SMTPAuthPass); 
		$SMTP.Send($Message)
	}
	Catch {
		Debug "Email ERROR : $($Error[0])"
	}
}

Function MySQLQuery($Query) {
	$Today = (Get-Date).ToString("yyyyMMdd")
	$DBErrorLog = "$PSScriptRoot\$Today-DBError.log"
	$ConnectionString = "server=" + $SQLHost + ";port=" + $SQLPort + ";uid=" + $SQLAdminUserName + ";pwd=" + $SQLAdminPassword + ";database=" + $SQLDatabase + ";SslMode=" + $SQLSSL + ";Default Command Timeout=" + $SQLCommandTimeOut + ";Connect Timeout=" + $SQLConnectTimeout + ";"
	Try {
		[void][System.Reflection.Assembly]::LoadWithPartialName("MySql.Data")
		$Connection = New-Object MySql.Data.MySqlClient.MySqlConnection
		$Connection.ConnectionString = $ConnectionString
		$Connection.Open()
		$Command = New-Object MySql.Data.MySqlClient.MySqlCommand($Query, $Connection)
		$DataAdapter = New-Object MySql.Data.MySqlClient.MySqlDataAdapter($Command)
		$DataSet = New-Object System.Data.DataSet
		$RecordCount = $dataAdapter.Fill($dataSet, "data")
		$DataSet.Tables[0]
	}
	Catch {
		Write-Output "$(Get-Date -f G) : ERROR : Unable to run query : $Query" | Out-File $DBErrorLog -append
		Write-Output "$(Get-Date -f G) : ERROR : $($Error[0])" | Out-File $DBErrorLog -append
	}
	Finally {
		$Connection.Close()
	}
}

<#  Get outside temperature from API  #>
$URL = "https://api.open-meteo.com/v1/forecast?latitude=" + $Latitude + "&longitude=" + $Longitude + "&current=temperature_2m&timezone=" + [uri]::EscapeDataString($PHPTimezone) + "&forecast_days=1"
$Weather = Invoke-WebRequest $URL -Method Get -UseBasicParsing | ConvertFrom-Json

<#  Get attic temperature from CSV  #>
$AtticTemp = [math]::Round(((Get-Content -Path $AtticTempCSV | Select -Last 1).Split(",")[1] -Replace "[^0-9-.]",""),1)

<#  Warn if no data  #>
If (!$AtticTemp) {
	$Msg = "Warning: Attic Temperature Monitor failed to read data!"
	ForEach ($Key in $Recip.Keys) {
		If ($UseSMS) {SendSMS $($Recip[$Key]) $Msg}
		If ($UseEmail) {SendEmail $Key $Msg}
	}
}

<#  Send High Temperature Warning  #>
If ([Int]$AtticTemp -ge $WarningTemp) {
	$Msg = "Warning: High Attic Temperature: Currently $AtticTemp degrees!"
	ForEach ($Key in $Recip.Keys) {
		If ($UseSMS) {SendSMS $($Recip[$Key]) $Msg}
		If ($UseEmail) {SendEmail $Key $Msg}
	}
}

<#  Get time to insert into db - replace with local timezone if TZ string messed up  #>
Try {$DateTime = $(([System.TimeZoneInfo]::ConvertTimeBySystemTimeZoneId((Get-Date), $PSTimeZone)).ToString('yyyy-MM-dd HH:00:00'))}
Catch {$DateTime = $(([System.TimeZoneInfo]::ConvertTimeBySystemTimeZoneId((Get-Date), (Get-TimeZone).ID)).ToString('yyyy-MM-dd HH:00:00'))}

<#  Insert into db  #>
$Query = "
	INSERT INTO temp (time, inside, outside)
	VALUES (
		'$DateTime',
		'$AtticTemp',
		'$($Weather.current.temperature_2m)'
	);"
MySQLQuery $Query

<#  Clear CSV  #>
Clear-Content -Path $AtticTempCSV -Force
