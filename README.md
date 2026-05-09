# EHNET käivitamise juhend Windowsis

## 1. Paigalda vajalik tarkvara

Paigalda arvutisse:

- Visual Studio Code - https://code.visualstudio.com/thank-you?dv=win64user
- Git - https://git-scm.com/install/windows
- XAMPP - https://www.apachefriends.org/
- Composer - https://getcomposer.org/download/  
  Lae alla `Composer-Setup.exe` ja paigalda Windowsi installeriga. Paigaldamisel vali PHP asukohaks `C:\xampp\php\php.exe`.
- Node.js LTS - https://nodejs.org/en/download  
  Lae alla ja paigalda Windowsi installeriga. Node.js paigaldusega tuleb kaasa ka npm.

XAMPP-is käivita:

- Apache
- MySQL

## 2. Luba vajalikud PHP extensionid

Projekt kasutab piltide töötlemist ja Composer vajab pakettide paigaldamiseks ZIP tuge. Seetõttu peavad PHP-s olema lubatud `gd` ja `zip` extensionid.

Ava fail:

```text
C:\xampp\php\php.ini
```

Leia read:

```ini
;extension=gd
;extension=zip
```

Eemalda mõlema rea algusest semikoolon:

```ini
extension=gd
extension=zip
```

Salvesta fail.

Seejärel taaskäivita XAMPP-is Apache ja ava uus PowerShelli aken.

Kontrolli PowerShellis:

```powershell
php -m | findstr gd
php -m | findstr zip
```

Kui kuvatakse `gd` ja `zip`, on vajalikud extensionid lubatud.

## 3. Kontrolli, et käsud töötavad

Ava PowerShell ja käivita:

```powershell
git --version
php -v
composer --version
node -v
npm.cmd -v
```

Kui `npm` ei tööta, kasuta edaspidi `npm.cmd`.

## 4. Klooni projekt GitHubist

```powershell
cd C:\
mkdir Projektid
cd Projektid
git clone SINU_REPO_LINK
cd ehnet
```

## 5. Ava projekt VS Code’is

```powershell
code .
```

Kui see ei tööta, ava VS Code käsitsi ja vali kloonitud projekti kaust.

## 6. Paigalda PHP sõltuvused

```powershell
composer install
```

## 7. Paigalda JavaScripti sõltuvused

```powershell
npm.cmd install
```

## 8. Loo `.env` fail

```powershell
copy .env.example .env
```

## 9. Genereeri rakenduse võti

```powershell
php artisan key:generate
```

## 10. Loo andmebaas

Kui MySQL root kasutajal parooli ei ole:

```powershell
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS ehnet CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Või loo andmebaas käsitsi phpMyAdminis:

```text
http://localhost/phpmyadmin
```

Andmebaasi nimi:

```text
ehnet
```

## 11. Kontrolli `.env` faili

`.env` failis peavad olema vähemalt järgmised seaded:

```env
APP_NAME=EHNET
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

APP_TIMEZONE=Europe/Tallinn
APP_LOCALE=et

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ehnet
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync

MAIL_MAILER=log
```

Kui MySQL kasutajal on parool, lisa see reale:

```env
DB_PASSWORD=sinu_parool
```

## 12. Loo andmebaasi tabelid ja algandmed

```powershell
php artisan migrate:fresh --seed
```

## 13. Loo storage link

```powershell
php artisan storage:link
```

## 14. Puhasta cache

```powershell
php artisan optimize:clear
```

## 15. Käivita frontend

Ava esimene PowerShelli aken projekti kaustas:

```powershell
npm.cmd run dev
```

Jäta see aken avatuks.

## 16. Käivita Laravel server

Ava teine PowerShelli aken projekti kaustas:

```powershell
php artisan serve
```

Ava brauseris:

```text
http://127.0.0.1:8000
```

## 17. Registreerimislingi leidmine

Kuna e-kirjad salvestatakse logisse, ava fail:

```text
storage/logs/laravel.log
```

Sealt leiab registreerimise lõpetamise lingi.

## 18. Kasulikud käsud

```powershell
php artisan route:list
php artisan optimize:clear
npm.cmd run build
php artisan migrate:fresh --seed
```

## Testimine

Käesolevas versioonis testiti rakendust manuaalselt. Automaattestid on planeeritud järgmisse arendusetappi.