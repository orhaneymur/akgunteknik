## Yapılanlar Kaydı

- (12.01.2026) Laragon PHP için `php.ini` kopyalandı ve `extension_dir` doğru dizine (`C:/laragon/bin/php/php-8.3.28-Win32-vs16-x64/ext`) ayarlandı; `openssl`, `fileinfo`, `zip` eklentileri aktifleştirildi.
- (12.01.2026) Laragon PHP + Composer ile yeni Laravel iskeleti kuruldu (`composer install` tamamlandı).
- (12.01.2026) `composer.json` PSR-4 autoload’a `Modules\\ => Modules/` eklendi ve `composer dump-autoload` çalıştırıldı.
- (12.01.2026) Modüler yapı için dizinler oluşturuldu: `Modules/Core/Traits` ve `Modules/Core/Http/Controllers`.
- (12.01.2026) Ortak JSON yanıt yapısı için `Modules/Core/Traits/ApiResponse.php` eklendi (success/data/message/errors alanlı standart response yardımcıları).
- (12.01.2026) Tüm modül controller’ları için temel sınıf olarak `Modules/Core/Http/Controllers/BaseController.php` eklendi; Laravel Controller’ını genişletir ve `ApiResponse` trait’ini kullanır.
- (12.01.2026) Modül servis sağlayıcılarını eklemek için öneri paylaşıldı: her modülün provider’ını `bootstrap/providers.php` dizisine ekleyerek yüklemek (Laravel 12 önerilen yaklaşım).
- (12.01.2026) SaaS çoklu kiracılı temel için Core migration'ları eklendi: `tenants`, `warehouses` (tenant FK, is_active), `branches` (tenant ve warehouse FK, address), `users` tablosu için yeni migration ile `tenant_id`, `branch_id` (nullable, nullOnDelete), `role` (default staff) eklendi; tenant_id ve branch_id indekslendi.
- (12.01.2026) `Modules/Core/Providers/CoreServiceProvider.php` oluşturuldu ve `bootstrap/providers.php`'ye eklendi; migration'ları `Modules/Core/Database/Migrations` dizininden yükler.
- (12.01.2026) Duplicate migration dosyaları temizlendi; migration'lar optimize edildi (foreign key ve index sıralaması düzeltildi).
- (12.01.2026) `Modules/Core/Database/Seeders/CoreDatabaseSeeder.php` oluşturuldu; "Golden Tenant" test verileri ekler: Tenant (Orhan Teknik, domain: orhanteknik), Warehouse (Merkez Depo), Branch (Merkez Şube), Admin User (admin@orhanteknik.com, password: "password", role: admin). Ana `DatabaseSeeder.php`'ye kaydedildi.
- (12.01.2026) Laravel Sanctum kuruldu ve yapılandırıldı; `User` model'ine `HasApiTokens` trait'i eklendi. `Modules/Core/Http/Controllers/AuthController.php` oluşturuldu (login metodu ile token döndürür). `Modules/Core/Routes/api.php` oluşturuldu (`POST /api/core/login` route'u). `CoreServiceProvider` güncellendi (route'ları `/api` prefix'i ile yükler).

## Bu projeyi bilmeyen biri için hızlı rehber

### Çalışma ortamı
- PHP ve Composer, Laragon içinden kullanılacak:  
  - PHP: `C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe`  
  - Composer: `C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe C:\laragon\bin\composer\composer.phar <komut>`
- Gerekli PHP eklentileri CLI’de açık olmalı: `openssl`, `fileinfo`, `zip`. Şu anda `php.ini`’de etkin.

### İlk kurulum (sıfırdan)
1) Depoyu klonla veya dosyaları al.  
2) Proje dizinine geç (`C:\Users\orhan.eymur\Desktop\akgunteknik`).  
3) Bağımlılıkları kur:  
   `C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe C:\laragon\bin\composer\composer.phar install`  
4) `.env` yoksa kopyala: `copy .env.example .env` (PowerShell’de `Copy-Item .env.example .env`).  
5) Uygulama anahtarı: `php artisan key:generate` (aynı PHP yolu ile).  
6) Veritabanı ayarlarını `.env` içinde düzenle ve gerekirse `php artisan migrate`.

### Projeyi çalıştırma / yeniden başlatma
- Geliştirici sunucusu:  
  `C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan serve`
- Queue veya başka servisler gerekiyorsa manuel başlatın (örn. `php artisan queue:listen`). Bu projede otomatik başlatma yapılmıyor, tercihen elle açılacak.
- Değişiklik sonrası autoload yenilemek gerekirse: `composer dump-autoload`.

### Modüler mimari (Modular Monolith)
- İş mantığı için default `app/` yerine `Modules/` dizini kullanılır. PSR-4 autoload: `Modules\\ => Modules/`.
- Ortak modül: `Modules/Core`
  - `Traits/ApiResponse.php`: tüm JSON cevapları `{success, data, message, errors}` yapısında döndürmek için yardımcılar.
  - `Http/Controllers/BaseController.php`: Laravel Controller’ı genişletir, `ApiResponse` trait’ini kullanır. Tüm modül controller’ları buradan türetebilir.

### Yeni modül ekleme (örn. Inventory)
1) Dizinleri oluştur: `Modules/Inventory/Http/Controllers`, `Modules/Inventory/Providers`, gerekirse `Services`, `Models`, `Routes`, `Database` vb.  
2) `Providers/InventoryServiceProvider.php` oluştur, ServiceProvider’dan extend et.  
3) Provider’ı `bootstrap/providers.php` dizisine ekle:  
   ```php
   return [
       App\Providers\AppServiceProvider::class,
       Modules\Inventory\Providers\InventoryServiceProvider::class,
   ];
   ```  
4) Route dosyalarını modül içinde tut (ör. `Modules/Inventory/Routes/api.php`) ve provider içinde yükle.  
5) Autoload güncellemesi gerekirse `composer dump-autoload`.

### Veritabanı Migration'ları (Multi-Tenant SaaS Yapısı)

#### Migration Dosyaları
Tüm Core migration'ları `Modules/Core/Database/Migrations/` dizininde bulunur:

1. **2026_01_12_000001_create_tenants_table.php**
   - `tenants` tablosu: Multi-tenant yapı için kiracı bilgileri
   - Alanlar: `id` (BigInt, PK), `company_name` (String), `domain_prefix` (String, Unique), `is_active` (Boolean, default true), `timestamps`

2. **2026_01_12_000002_create_warehouses_table.php**
   - `warehouses` tablosu: Fiziksel stok lokasyonları
   - Alanlar: `id` (BigInt, PK), `tenant_id` (FK to tenants, indexed), `name` (String), `is_active` (Boolean), `timestamps`
   - Foreign Key: `tenant_id` -> `tenants.id` (cascade on delete/update)

3. **2026_01_12_000003_create_branches_table.php**
   - `branches` tablosu: Satış/hizmet noktaları
   - Alanlar: `id` (BigInt, PK), `tenant_id` (FK to tenants, indexed), `warehouse_id` (FK to warehouses, indexed), `name` (String), `address` (Text, nullable), `timestamps`
   - Foreign Keys: `tenant_id` -> `tenants.id`, `warehouse_id` -> `warehouses.id` (cascade on delete/update)
   - **ÖNEMLİ**: Branch'ler bir warehouse'a bağlıdır (stok kaynağı)

4. **2026_01_12_000004_update_users_table_add_tenant_branch_role.php**
   - `users` tablosunu günceller (mevcut Laravel users tablosuna eklemeler)
   - Eklenen alanlar:
     - `tenant_id` (FK to tenants, indexed, required)
     - `branch_id` (FK to branches, indexed, nullable, nullOnDelete)
     - `role` (String, default: 'staff')

#### Migration Komutları

**Migration'ları çalıştırma:**
```bash
C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan migrate
```

**Migration durumunu kontrol:**
```bash
C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan migrate:status
```

**Migration'ları geri alma (rollback):**
```bash
C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan migrate:rollback
```

**Tüm migration'ları sıfırlama (DİKKAT: Veriler silinir):**
```bash
C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan migrate:fresh
```

#### Migration Sırası
Migration'lar timestamp sırasına göre çalışır:
1. `tenants` (önce oluşturulmalı)
2. `warehouses` (tenants'a bağlı)
3. `branches` (tenants ve warehouses'a bağlı)
4. `users` güncellemesi (tenants ve branches'a bağlı)

#### Performans Optimizasyonları
- `tenant_id` alanları tüm ilgili tablolarda indekslenmiştir (hızlı sorgular için)
- Foreign key constraint'ler cascade on delete/update ile yapılandırılmıştır
- `branch_id` nullable olduğu için `nullOnDelete` kullanılmıştır (branch silinirse user'ın branch_id'si null olur)

### Veritabanı Seeder'ları (Test Verileri)

#### CoreDatabaseSeeder
**Dosya:** `Modules/Core/Database/Seeders/CoreDatabaseSeeder.php`

**Oluşturduğu Veriler:**
1. **Tenant:** "Orhan Teknik" (domain_prefix: "orhanteknik")
2. **Warehouse:** "Merkez Depo" (tenant'a bağlı)
3. **Branch:** "Merkez Şube" (tenant ve warehouse'a bağlı)
4. **Admin User:**
   - Name: "Orhan Admin"
   - Email: "admin@orhanteknik.com"
   - Password: "password" (hashed)
   - Tenant ID: Orhan Teknik'in ID'si
   - Branch ID: Merkez Şube'nin ID'si
   - Role: "admin"

#### Seeder Komutları

**Tüm seeder'ları çalıştırma (DatabaseSeeder):**
```bash
C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan db:seed
```

**Sadece CoreDatabaseSeeder'ı çalıştırma:**
```bash
C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan db:seed --class=Modules\\Core\\Database\\Seeders\\CoreDatabaseSeeder
```

**Migration + Seeder birlikte (fresh start):**
```bash
C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan migrate:fresh --seed
```

#### Seeder Kaydı
`CoreDatabaseSeeder` ana `DatabaseSeeder.php` dosyasına kayıtlıdır. `php artisan db:seed` komutu otomatik olarak CoreDatabaseSeeder'ı çalıştırır.

**DatabaseSeeder.php içeriği:**
```php
public function run(): void
{
    $this->call(CoreDatabaseSeeder::class);
}
```

#### Güvenlik Notu
Seeder'da oluşturulan admin kullanıcısının şifresi "password" olarak ayarlanmıştır. **Production ortamında mutlaka değiştirin!**

### API Authentication (Laravel Sanctum)

#### Kurulum
**Sanctum paketi kuruldu:**
```bash
C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe C:\laragon\bin\composer\composer.phar require laravel/sanctum
```

**Config dosyası publish edildi:**
```bash
C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

#### Yapılandırma
- **User Model:** `app/Models/User.php` dosyasına `HasApiTokens` trait'i eklendi
- **Migration:** Sanctum migration'ları `database/migrations` dizinine kopyalandı (personal_access_tokens tablosu)
- **Config:** `config/sanctum.php` dosyası oluşturuldu

#### API Login Endpoint
**Route:** `POST /api/core/login`

**Request Body:**
```json
{
    "email": "admin@orhanteknik.com",
    "password": "password"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "data": {
        "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
    },
    "message": "Login success",
    "errors": null
}
```

**Error Response (401):**
```json
{
    "success": false,
    "data": null,
    "message": "Invalid credentials",
    "errors": {
        "email": ["The provided credentials are incorrect."]
    }
}
```

#### Dosya Yapısı
- **Controller:** `Modules/Core/Http/Controllers/AuthController.php`
  - `login()` metodu: Email/password doğrulama, token oluşturma, standart JSON response döndürme
- **Route:** `Modules/Core/Routes/api.php`
  - `POST /core/login` route'u tanımlı
- **ServiceProvider:** `Modules/Core/Providers/CoreServiceProvider.php`
  - Route'ları `/api` prefix'i ile yükler

#### Token Kullanımı
API isteklerinde token'ı header'da gönderin:
```
Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

#### Test Komutu (cURL)
```bash
curl -X POST http://localhost:8000/api/core/login \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"admin@orhanteknik.com\",\"password\":\"password\"}"
```

### Sorun giderme
- PHP eklentisi hataları (openssl/fileinfo/zip) için `php.ini`'de `extension_dir` ayarı: `C:/laragon/bin/php/php-8.3.28-Win32-vs16-x64/ext`.  
- TLS/SSL hatası alırsan openssl'in etkin olduğundan emin ol ve Laragon PHP yolunu kullandığından emin ol.  
- Proje dizini boş değil uyarısı: Kurulum yarım kaldıysa dizini temizleyip yeniden `composer create-project` veya mevcut dosyalarla `composer install` çalıştır.
- Migration hatası: `CoreServiceProvider`'ın `bootstrap/providers.php`'de kayıtlı olduğundan emin ol. Migration'lar otomatik olarak `Modules/Core/Database/Migrations` dizininden yüklenir.

