# EHNET käivitamise juhend Windowsis

EHNET on Laravelil põhinev veebiplatvorm ehitusmaterjalide ja nende jääkide ostmiseks, müümiseks ning uuesti kasutusse suunamiseks. Projekt valmis lõputöö praktilise osana prototüübina.

## 1. Vajalik tarkvara

Enne projekti käivitamist paigalda arvutisse järgmised programmid.

### 1.1 Visual Studio Code

Visual Studio Code on vajalik projekti failide vaatamiseks ja muutmiseks.

Pärast paigaldamist saab projekti kausta avada käsuga:

```powershell
code .
```

### 1.2 Git

Git on vajalik projekti allalaadimiseks GitHubist.

Kontrolli paigaldust PowerShellis:

```powershell
git --version
```

### 1.3 XAMPP

XAMPP sisaldab Apache veebiserverit, MySQL andmebaasi, PHP-d ja phpMyAdmini.

Pärast XAMPP-i paigaldamist ava **XAMPP Control Panel** ja käivita:

- Apache
- MySQL

phpMyAdmin avaneb brauseris aadressil:

```text
http://localhost/phpmyadmin
```

### 1.4 PHP

PHP tuleb XAMPP-iga kaasa. Selleks, et Laraveli käsud töötaksid PowerShellis, peab PHP olema lisatud Windowsi PATH keskkonnamuutujasse.

XAMPP-i PHP asub tavaliselt siin:

```text
C:\xampp\php
```

Kontrolli PowerShellis:

```powershell
php -v
```

Kui kuvatakse PHP versioon, on PHP õigesti seadistatud.

### 1.5 Composer

Composer on vajalik Laraveli PHP pakettide paigaldamiseks.

Kontrolli PowerShellis:

```powershell
composer --version
```

Kui Composer küsib paigaldamisel PHP asukohta, vali:

```text
C:\xampp\php\php.exe
```

### 1.6 Node.js ja npm

Node.js ja npm on vajalikud frontendi failide jaoks.

Kontrolli PowerShellis:

```powershell
node -v
npm -v
```

Kui PowerShell ei luba `npm` käsku kasutada, kasuta edaspidi `npm.cmd` varianti.

Näiteks:

```powershell
npm.cmd install
npm.cmd run dev
npm.cmd run build
```

## 2. Projekti allalaadimine

Ava PowerShell kaustas, kuhu soovid projekti alla laadida.

Näide:

```powershell
cd C:\Aigar\Projektid
```

Klooni projekt GitHubist:

```powershell
git clone REPOSITOORIUMI_LINK
```

Liigu projekti kausta:

```powershell
cd ehnet
```

Ava projekt Visual Studio Code’is:

```powershell
code .
```

## 3. PHP sõltuvuste paigaldamine

Käivita projekti kaustas:

```powershell
composer install
```

## 4. JavaScripti sõltuvuste paigaldamine

Käivita:

```powershell
npm.cmd install
```

## 5. Keskkonnafaili loomine

Kopeeri `.env.example` fail uueks `.env` failiks:

```powershell
copy .env.example .env
```

Genereeri rakenduse võti:

```powershell
php artisan key:generate
```

## 6. Andmebaasi loomine

Ava brauseris phpMyAdmin:

```text
http://localhost/phpmyadmin
```

Loo uus andmebaas nimega:

```text
ehnet
```

Soovi korral vali võrdluseks:

```text
utf8mb4_unicode_ci
```

## 7. Andmebaasi seadistamine

Ava `.env` fail ja kontrolli, et andmebaasi seaded oleksid järgmised:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ehnet
DB_USERNAME=root
DB_PASSWORD=
```

Kui sinu MySQL kasutajal on parool, lisa see reale:

```env
DB_PASSWORD=sinu_parool
```

Kontrolli ka, et rakenduse aadress oleks lokaalse serveri jaoks:

```env
APP_URL=http://127.0.0.1:8000
```

Arenduskeskkonnas kasutatakse e-kirjade jaoks logifaili:

```env
MAIL_MAILER=log
```

Sellisel juhul ei saadeta e-kirju päriselt välja, vaid need salvestatakse logifaili.

## 8. Andmebaasi tabelite ja algandmete loomine

Käivita:

```powershell
php artisan migrate:fresh --seed
```

See loob andmebaasi tabelid ja lisab algandmed.

## 9. Storage lingi loomine

Kuulutuste piltide ja teiste failide kuvamiseks käivita:

```powershell
php artisan storage:link
```

Kui link on juba olemas, võib Laravel kuvada teate, et link on juba loodud. See ei ole probleem.

## 10. Cache puhastamine

Käivita:

```powershell
php artisan optimize:clear
```

## 11. Frontendi käivitamine

Ava esimene PowerShelli aken projekti kaustas ja käivita:

```powershell
npm.cmd run dev
```

Jäta see aken avatuks.

## 12. Laraveli serveri käivitamine

Ava teine PowerShelli aken projekti kaustas ja käivita:

```powershell
php artisan serve
```

Kui server käivitub, kuvatakse aadress umbes sellisel kujul:

```text
http://127.0.0.1:8000
```

Ava see aadress brauseris.

## 13. Rakenduse kontrollimine

Pärast käivitamist kontrolli järgmisi samme:

1. avaleht avaneb;
2. registreerimisvorm avaneb;
3. kasutaja saab sisestada e-posti aadressi;
4. registreerimise e-kiri salvestub logifaili;
5. kasutaja saab registreerimise lõpule viia;
6. kasutaja saab sisse logida;
7. kasutaja saab lisada kuulutuse;
8. kuulutus kuvatakse kuulutuste vaates.

Kui `MAIL_MAILER=log`, asub registreerimise e-kiri failis:

```text
storage/logs/laravel.log
```

Ava see fail ja otsi sealt registreerimise lõpetamise link.

## 14. Kasulikud käsud

Route’ide kontrollimiseks:

```powershell
php artisan route:list
```

Cache’i puhastamiseks:

```powershell
php artisan optimize:clear
```

Frontendi tootmisversiooni ehitamiseks:

```powershell
npm.cmd run build
```

Andmebaasi uuesti loomiseks:

```powershell
php artisan migrate:fresh --seed
```

## 15. Testimine

Käesolevas versioonis testiti rakendust manuaalselt. Kontrolliti registreerimist, sisselogimist, kuulutuste lisamist ja haldamist, vestlusi, ostusoove, tehinguid, lemmikuid, tagasisidet, vormide valideerimist, mobiilivaadet ja ligipääsuõigusi.

Automaattestid on planeeritud järgmisse arendusetappi.

## 16. Märkused

Tegemist on lõputöö raames loodud prototüübiga. Enne avalikku kasutuselevõttu vajab rakendus täiendavat testimist, turvalisuse ülevaatust, kasutajaliidese viimistlust ja haldusliidese arendamist.