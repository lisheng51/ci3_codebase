## Omgeving
Apache 2.4;
PHP 8.4;
MariaDB 10.4
NodeJS 16; 
Composer 2;


## Voorbereiden
Maak een database aan, alle bestanden in readme map naar root kopieren, opent 'env.php', pas volgende database instelling aan:

$domainSetting['databaseHost'] ?? '...';     
$domainSetting['databaseName'] ?? '...';
$domainSetting['databaseUser'] ?? '...';
$domainSetting['databasePass'] ?? '...';


## Dependencies installeren
Opent PowerShell in root, voert volgende opdracht uit:
'npm i' 

Als klaar is, voert volgende opdracht uit:
'cd .\application\modules\webmaster\'
enter en dan voert volgende opdracht uit:
'composer i'

als geen module webmaster gevonden, graag module webmaster aanmaken en volgende opdracht een voor een uitvoeren:
composer require picqer/php-barcode-generator
composer require chillerlan/php-qrcode
composer require phpmailer/phpmailer
composer require guzzlehttp/guzzle
composer require mpdf/mpdf

als package.json is aanwezig in module webmaster, voert volgende opdracht uit:
'npm i' 


## Data installeren
Ga naar: /site/setup maak admin aan, daarna als webmaster inloggen
