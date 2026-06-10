
## Priekšnosacījumi

Pirms instalācijas pārliecinieties, ka jūsu sistēmā ir:

- **PHP 8.1 vai jaunāks**
- **Composer** (PHP atkarību pārvaldnieks)
- **MySQL/MariaDB** datu bāze
- **Laragon** (ieteicams Windows lietotājiem) vai **XAMPP** / **WAMP**

### Laragon lietotājiem

Laragon parasti jau ir iekļauts nepieciešamais PHP un MySQL. Pārliecinieties, ka ir aktivizēti šādi PHP paplašinājumi:
- `php_zip`
- `php_xml`
- `php_fileinfo`

Aktivizēšanai: Laragon → PHP → Extensions → atzīmējiet nepieciešamos.

## Instalācija

### 1. Projekta lejupielāde

```bash
git clone https://github.com/DidzisWow/Classroom
cd classroom

## Atkarību instalācija
composer install

## Konfigurējiet datu bāzes savienojumu .env failā:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306 
DB_DATABASE=Nigga
DB_USERNAME=root
DB_PASSWORD=

## Lietojumprogrammas atslēgas ģenerēšana
php artisan key:generate

### Datu bāzes migrācijas
php artisan migrate:fresh --seed

### Izveidojiet storage symlink
php artisan storage:link

## Servera palaišana
php artisan serve

### Atveriet pārlūku un dodieties uz: http://127.0.0.1:8000