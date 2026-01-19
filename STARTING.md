# 🚀 Akgün Teknik ERP - Başlangıç Rehberi

Bu dosya, projeyi sıfırdan başlatmak için gereken tüm adımları içerir.

---

## 📋 Önkoşullar

Aşağıdaki servislerin çalışıyor olması gerekiyor:

1. **MySQL Veritabanı** (Laragon üzerinden)
   - Laragon'u açın ve MySQL'in çalıştığından emin olun
   - Veritabanı: `akgunteknik` (veya `.env` dosyasında belirtilen)

2. **PHP 8.3.28** (Laragon üzerinden)
   - Yolu: `C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe`

3. **Node.js ve npm** (Vite için gerekli)
   - `node --version` ve `npm --version` ile kontrol edin

---

## 🎯 Adım Adım Başlatma

### 0️⃣ PHP Alias Oluştur (İsteğe Bağlı - Kolaylık İçin)

**ÖNEMLİ:** PHP PATH'te değilse, her komutta tam yolu kullanmanız gerekir. Kolaylık için PowerShell'de bir alias oluşturabilirsiniz:

```powershell
# Mevcut PowerShell oturumu için alias (geçici)
Set-Alias -Name php -Value "C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe"

# Kalıcı yapmak için PowerShell profil dosyanıza ekleyin:
# notepad $PROFILE
# Dosyaya şu satırı ekleyin:
# Set-Alias -Name php -Value "C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe"
```

**Alternatif:** Alias oluşturmak istemiyorsanız, tüm `php` komutlarını `C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe` ile değiştirin.

---

### 1️⃣ Proje Dizinine Git

PowerShell veya Terminal'i açın ve proje dizinine gidin:

```powershell
cd C:\Users\orhan.eymur\Desktop\akgunteknik
```

---

### 2️⃣ Ortam Değişkenlerini Kontrol Et

`.env` dosyasının mevcut olduğundan ve doğru yapılandırıldığından emin olun:

```powershell
# .env dosyasını kontrol et
Get-Content .env | Select-String "DB_"
```

**Önemli:** `.env` dosyasında şunlar olmalı:
- `APP_KEY=` (boş olmamalı)
- `DB_CONNECTION=mysql`
- `DB_DATABASE=akgunteknik` (veya kendi veritabanı adınız)
- `DB_USERNAME=root` (veya MySQL kullanıcı adınız)
- `DB_PASSWORD=` (MySQL şifreniz, boşsa boş bırakın)

Eğer `.env` yoksa:
```powershell
Copy-Item .env.example .env
C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan key:generate
```

---

### 3️⃣ Veritabanını Kontrol Et ve Hazırla

**a) Veritabanının var olduğundan emin olun:**

MySQL'e bağlanın ve veritabanını oluşturun (eğer yoksa):

```sql
CREATE DATABASE IF NOT EXISTS akgunteknik CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**b) Migration'ları çalıştırın (eğer daha önce çalıştırmadıysanız):**

```powershell
# PHP alias oluşturduysanız:
php artisan migrate

# Veya tam yol ile:
C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan migrate
```

**c) Seeder'ları çalıştırın (test verileri için):**

```powershell
# PHP alias oluşturduysanız:
php artisan db:seed

# Veya tam yol ile:
C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan db:seed
```

Bu komut şunları oluşturur:
- Tenant: "Orhan Teknik" (domain: orhanteknik)
- Warehouse: "Merkez Depo"
- Branch: "Merkez Şube"
- Admin User: `admin@orhanteknik.com` / `password`

---

### 4️⃣ NPM Bağımlılıklarını Kontrol Et

Eğer `node_modules` klasörü yoksa veya eksik paketler varsa:

```powershell
npm install
```

Bu komut şunları kurar:
- Vue.js 3
- Vite
- Tailwind CSS
- Ve diğer tüm bağımlılıklar

---

### 5️⃣ Laravel Sunucusunu Başlat

**Yeni bir PowerShell/Terminal penceresi açın** ve şu komutu çalıştırın:

```powershell
cd C:\Users\orhan.eymur\Desktop\akgunteknik

# PHP alias oluşturduysanız:
php artisan serve

# Veya tam yol ile:
C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan serve
```

**Beklenen çıktı:**
```
INFO  Server running on [http://127.0.0.1:8000]
```

**Önemli:** Bu pencereyi açık tutun! Kapatırsanız sunucu durur.

---

### 6️⃣ Vite Dev Server'ı Başlat

**Başka bir yeni PowerShell/Terminal penceresi açın** ve şu komutu çalıştırın:

```powershell
cd C:\Users\orhan.eymur\Desktop\akgunteknik
npm run dev
```

**Beklenen çıktı:**
```
VITE v7.x.x  ready in xxx ms
  ➜  Local:   http://localhost:5173/
  ➜  Network: use --host to expose
  LARAVEL v12.46.0  plugin v2.0.1
  ➜  APP_URL: http://localhost
```

**Önemli:** Bu pencereyi de açık tutun! Kapatırsanız Vite durur ve Vue.js/Tailwind CSS çalışmaz.

---

## ✅ Kontrol Listesi

Başlatmadan önce şunları kontrol edin:

- [ ] Laragon açık ve MySQL çalışıyor
- [ ] `.env` dosyası mevcut ve doğru yapılandırılmış
- [ ] `APP_KEY` `.env` dosyasında mevcut
- [ ] Veritabanı oluşturulmuş
- [ ] Migration'lar çalıştırılmış (PHP tam yolu ile `artisan migrate`)
- [ ] Seeder'lar çalıştırılmış (PHP tam yolu ile `artisan db:seed`)
- [ ] `node_modules` klasörü mevcut (`npm install` çalıştırılmış)

---

## 🌐 Erişim Adresleri

Uygulama başlatıldıktan sonra şu adreslerden erişebilirsiniz:

### Frontend (Vue.js SPA)
- **Ana Sayfa:** http://127.0.0.1:8000
- Bu sayfa Vue.js 3 + Tailwind CSS ile çalışır

### API Endpoints
- **Login:** POST http://127.0.0.1:8000/api/core/login
  - Body: `{"email": "admin@orhanteknik.com", "password": "password"}`
  
- **Ürünler:** GET http://127.0.0.1:8000/api/inventory/products
  - Header: `Authorization: Bearer {token}` (login'den alınan token)

- **Müşteriler:** GET http://127.0.0.1:8000/api/contact/customers
  - Header: `Authorization: Bearer {token}`

- **Tedarikçiler:** GET http://127.0.0.1:8000/api/contact/suppliers
  - Header: `Authorization: Bearer {token}`

---

## 🔧 Sorun Giderme

### Laravel sunucusu başlamıyor

**Hata:** `Port 8000 is already in use`

**Çözüm:**
```powershell
# Farklı bir port kullan
C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan serve --port=8001
```

Veya port 8000'i kullanan uygulamayı kapatın.

---

### Vite dev server başlamıyor

**Hata:** `EADDRINUSE: address already in use :::5173`

**Çözüm:**
```powershell
# Port 5173'ü kullanan işlemi bul ve kapat
netstat -ano | findstr :5173
taskkill /PID {PID_NUMARASI} /F
```

---

### PHP komutu tanınmıyor

**Hata:** `php : The term 'php' is not recognized as the name of a cmdlet...`

**Çözüm:**
1. PHP PATH'e eklenmemiş, Laragon'un PHP'sini kullanmalısınız
2. Her komutta tam yolu kullanın: `C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe`
3. Veya PowerShell'de alias oluşturun (yukarıdaki "0️⃣ PHP Alias Oluştur" bölümüne bakın)
4. **Hızlı çözüm:** PowerShell'de şu komutu çalıştırın:
   ```powershell
   Set-Alias -Name php -Value "C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe"
   ```
   Bu alias sadece mevcut PowerShell oturumu için geçerlidir. Kalıcı yapmak için PowerShell profil dosyanıza ekleyin.

---

### Veritabanı bağlantı hatası

**Hata:** `SQLSTATE[HY000] [1045] Access denied for user...`

**Çözüm:**
1. `.env` dosyasını kontrol edin
2. MySQL kullanıcı adı ve şifresini doğrulayın
3. Laragon'da MySQL'in çalıştığından emin olun

---

### Vue.js component'leri görünmüyor

**Hata:** Sayfa boş görünüyor veya Vue component render olmuyor

**Çözüm:**
1. Vite dev server'ın çalıştığından emin olun (`npm run dev`)
2. Tarayıcı konsolunu kontrol edin (F12)
3. Vite'in doğru port'ta çalıştığını kontrol edin (5173)
4. Laravel sunucusunun da çalıştığından emin olun

---

### Tailwind CSS stilleri uygulanmıyor

**Hata:** CSS stilleri görünmüyor

**Çözüm:**
1. Vite dev server'ın çalıştığından emin olun
2. Tarayıcı cache'ini temizleyin (Ctrl+F5)
3. `resources/css/app.css` dosyasının `@import 'tailwindcss';` içerdiğini kontrol edin

---

## 📝 Hızlı Başlatma (Özet)

Tüm adımları tek seferde çalıştırmak için:

**Terminal 1 (Laravel):**
```powershell
cd C:\Users\orhan.eymur\Desktop\akgunteknik

# PHP alias oluşturduysanız:
php artisan serve

# Veya tam yol ile:
C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan serve
```

**Terminal 2 (Vite):**
```powershell
cd C:\Users\orhan.eymur\Desktop\akgunteknik
npm run dev
```

Her iki terminal de açık kalmalı!

---

## 🛑 Uygulamayı Durdurma

Uygulamayı durdurmak için:

1. **Laravel sunucusunu durdurmak:** Terminal 1'de `Ctrl+C` tuşlarına basın
2. **Vite dev server'ı durdurmak:** Terminal 2'de `Ctrl+C` tuşlarına basın

---

## 📚 Ek Komutlar

### Cache Temizleme
```powershell
# PHP alias oluşturduysanız:
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Veya tam yol ile:
$php = "C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe"
& $php artisan cache:clear
& $php artisan config:clear
& $php artisan route:clear
& $php artisan view:clear
```

### Veritabanını Sıfırlama (DİKKAT: Tüm veriler silinir!)
```powershell
# PHP alias oluşturduysanız:
php artisan migrate:fresh --seed

# Veya tam yol ile:
C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan migrate:fresh --seed
```

### Composer Autoload Yenileme
```powershell
# Composer için de tam yol gerekebilir:
C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe C:\laragon\bin\composer\composer.phar dump-autoload
```

### NPM Build (Production için)
```powershell
npm run build
```

---

## 🎓 İlk Kullanım

1. Tarayıcıda http://127.0.0.1:8000 adresine gidin
2. Vue.js 3 + Tailwind CSS kurulum mesajını görmelisiniz
3. API testi için `public/test-login.html` dosyasını açabilirsiniz
4. Login yaparak token alabilir ve diğer API endpoint'lerini test edebilirsiniz

---

## 📞 Yardım

Sorun yaşarsanız:
1. Bu dosyadaki "Sorun Giderme" bölümünü kontrol edin
2. Terminal çıktılarını kontrol edin
3. Tarayıcı konsolunu kontrol edin (F12)
4. Laravel log dosyalarını kontrol edin: `storage/logs/laravel.log`

---

**Son Güncelleme:** 14 Ocak 2026  
**Proje:** Akgün Teknik ERP  
**Versiyon:** Laravel 12.46.0 + Vue.js 3 + Tailwind CSS v4

