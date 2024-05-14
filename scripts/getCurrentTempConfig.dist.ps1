<#

.SYNOPSIS
	Temperature Update Handler Config

.DESCRIPTION
	Show temperature as measured by TEMPerHUM/TEMPerX USB devices - Handler Config

.FUNCTIONALITY
	Handler script that inserts temperature data into database.
	
	Run once per hour from windows task scheduler.

.NOTES
	Two timezone settings are required because the weather API uses PHP format and the handler uses Powershell format for database insert.

	See Function SendSMS in getCurrentTemp.ps1 - delete custom function and enable generic one!! Twilio account required!
	
	Depending on your device, you may have to change the way Powershell reads the CSV output. 

.EXAMPLE
	Depending on your device, you may have to change the way Powershell reads the CSV output. 
	
	CSV output is comma separated. For temperature only models, the temperature comes second as so:
	$AtticTemp = [math]::Round(((Get-Content -Path $AtticTempCSV | Select -Last 1).Split(",")[1] -Replace "[^0-9-.]",""),1)
	
	Note "Split(",")[1]" which is the temperature reading in the CSV - humidity or outside temperature could be a different iteration. Change the iterator if that's the case.

#>

###   SCRIPT VARIABLES   ###
$InsideTempCSV      = "C:\scripts\TEMPerX\1.csv"      # Path to USB thermometer output CSV
$Latitude           = "47.5073"                       # Required for open-meteo.com weather API
$Longitude          = "21.6494"                       # Required for open-meteo.com weather API
$PHPTimeZone        = "Europe/Budapest"               # Required for open-meteo.com weather API - https://www.php.net/manual/en/timezones.php
$PSTimeZone         = "Central Europe Standard Time"  # Timezone setting to offset server timezone in database - ensures temperature reading in local timezone - Use Powershell "Get-TimeZone -ListAvailable" to determine correct timezone ID
[Int]$WarningTemp   = 45                              # Any temp reading higher than this will send a warning
$UseEmail           = $False                          # Send notifications by email
$UseSMS             = $True                           # Send notifications by SMS - NOTE!! Twilio account required for SMS
$Recip = @{                                           # Who should be notified? Array of email addresses to mobile numbers
	"user1@domain.com" = "+36301234567"
	"user2@gmail.com"  = "+36707654321"
}

<###   TWILIO VARIABLES   ###>
$SID                = "AC..............................2c"
$Token              = "27..........................23"
$SMSFrom            = "+36705551212"

<###   EMAIL VARIABLES   ###>
$EmailFrom          = "notify@domain.com"
$Subject            = "Hőmérséklet-figyelő értesítés"
$SMTPServer         = "mail.domain.com"
$SMTPAuthUser       = "notify@domain.com"
$SMTPAuthPass       = "supersecretpassword"
$SMTPPort           =  587
$SSL                = $True                           # If true, will use tls connection to send email

###   MYSQL VARIABLES   ###
$SQLAdminUserName   = 'tempmonitorUsername'
$SQLAdminPassword   = 'supersecretpassword'
$SQLDatabase        = 'tempmonitorDBname'
$SQLHost            = 'localhost'
$SQLPort            =  3306
$SQLSSL             = 'none'
$SQLConnectTimeout  = 300
$SQLCommandTimeOut  = 9000000                         # Leave high if read errors
