# Fycbit Ayrintili Kurulum Rehberi

Bu rehber Ubuntu 24.04 uzerinde yerel gelistirme kurulumu icindir. Gercek para,
mainnet anahtari, KYC verisi veya uretim veritabani kullanmayin.

## Sistem Mimarisi

Kurulum dort servisten olusur:

1. MySQL: uygulama ve wallet-service veritabani
2. Redis: cache ve kuyruklar
3. Laravel backend: varsayilan `127.0.0.1:8000`
4. Next.js web: varsayilan `localhost:3000`

Wallet-service ayri bir Node.js servisidir ve temel web kurulumu dogrulanmadan
baslatilmamalidir.

## 1. Sunucuyu Hazirlama

En az 4 CPU, 8 GB RAM ve 30 GB bos disk onerilir.

```bash
sudo apt update
sudo apt upgrade -y
sudo apt install -y git curl unzip mysql-server redis-server \
  php8.3-cli php8.3-fpm php8.3-mysql php8.3-bcmath php8.3-curl \
  php8.3-xml php8.3-mbstring php8.3-zip php8.3-gd php8.3-intl
sudo systemctl enable --now mysql redis-server
```

Composer:

```bash
curl -sS https://getcomposer.org/installer | php
sudo install -m 0755 composer.phar /usr/local/bin/composer
```

Node.js 18 LTS ve npm kurulduktan sonra:

```bash
php -v
composer --version
node --version
npm --version
mysql --version
redis-cli ping
```

Redis cevabi `PONG` olmalidir.

## 2. Kaynak Kodu Indirme

```bash
git clone --branch staging-publication --single-branch \
  https://github.com/FadilAltunkaynak/Fycbit.git
cd Fycbit
```

## 3. Veritabanini Hazirlama

```bash
sudo mysql
```

```sql
CREATE DATABASE fycbit CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'fycbit'@'localhost' IDENTIFIED BY 'yerel-guclu-parola';
GRANT ALL PRIVILEGES ON fycbit.* TO 'fycbit'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## 4. Backend Kurulumu

```bash
cd apps/backend
cp .env.example .env
chmod 600 .env
nano .env
```

`DB_PASSWORD` degerini olusturdugunuz yerel parola ile degistirin.

```bash
composer install
php artisan key:generate
php artisan config:clear
php artisan cache:clear
php artisan migrate
php artisan migrate:status
php artisan about
php artisan serve --host=127.0.0.1 --port=8000
```

Yeni terminalde:

```bash
curl -I http://127.0.0.1:8000
```

HTTP cevabi alinmadan web kurulumuna gecmeyin.

## 5. Web Kurulumu

```bash
cd apps/web
cp .env.example .env.local
npm ci
npm run build
npm run dev
```

Tarayicida `http://localhost:3000` adresini acin. Backend istekleri
`NEXT_PUBLIC_BASE_URL=http://127.0.0.1:8000` adresine gitmelidir.

`NEXT_PUBLIC_*` degerleri tarayiciya aciktir. Bu alanlara gercek API secret,
wallet anahtari veya uretim credential yazmayin.

## 6. Kuyruk Calisanlari

Backend temel islemleri dogrulandiktan sonra:

```bash
cd apps/backend
php artisan queue:work --queue=default --tries=1 --timeout=300
```

Horizon kullanilacaksa:

```bash
php artisan horizon
php artisan horizon:status
```

Canli ticaret kuyruklarini test verisi olmadan baslatmayin.

## 7. Wallet Service

Bu servis testnet/devnet disinda baslatilmamalidir.

```bash
cd apps/wallet-service
cp .env.example .env
chmod 600 .env
nano .env
npm ci
npx prisma generate
npm run type:check
npm run build
npm run dev
```

`DATABASE_URL`, `JWT_SECRET` ve `API_SECRET` yalnizca yerel test degerleri
olmali. Block processor alanlari varsayilan olarak kapali kalmalidir.

## 8. Tam Dogrulama

```bash
cd apps/backend
php artisan test
php artisan migrate:status

cd ../web
npm run build

cd ../wallet-service
npm run type:check
npm run build
```

Herhangi bir komut hata verirse uretim kurulumuna gecmeyin.

## Servis Baslatma Sirasi

1. MySQL
2. Redis
3. Laravel backend
4. Laravel queue/Horizon
5. Next.js web
6. Wallet-service
7. Websocket servisi ve istege bagli moduller

## Sik Sorunlar

`Access denied for user`:
`.env` icindeki DB kullanicisi/parolasi ile MySQL kullanicisini eslestirin.

`No application encryption key`:
`php artisan key:generate` calistirin.

`Class not found`:
`composer install` ve `composer dump-autoload` calistirin.

`NEXT_PUBLIC_BASE_URL value not found`:
`apps/web/.env.local` dosyasini olusturun ve web servisini yeniden baslatin.

`Prisma schema engine error`:
Node surumunu kontrol edin, `npm ci` ve `npx prisma generate` islemlerini
yeniden calistirin.

`Redis connection refused`:
`sudo systemctl status redis-server` ve `redis-cli ping` ile kontrol edin.

## Uretim Oncesi Zorunlu Islemler

- Clean-room kurulum testi
- Tum test ve build komutlarinin gecmesi
- Secret scanner ve dependency audit
- Lisans/sahiplik incelemesi
- Mainnet ve custody guvenlik denetimi
- Yedekten geri donus testi
- Nginx, TLS, firewall ve rate-limit yapilandirmasi

