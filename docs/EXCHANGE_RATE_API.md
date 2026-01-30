# Döviz Kuru API Entegrasyonu

Sistem, döviz kurlarını otomatik olarak API'den çekip veritabanına kaydeder.

## Özellikler

- ✅ **TCMB (Türkiye Cumhuriyet Merkez Bankası) API** - Ücretsiz, resmi kaynak
- ✅ **ExchangeRate-API** - Alternatif kaynak (fallback)
- ✅ **Otomatik güncelleme** - Her gün saat 09:00'da otomatik çalışır
- ✅ **Manuel çalıştırma** - İstediğiniz zaman manuel olarak çalıştırabilirsiniz

## Kullanım

### Manuel Çalıştırma

```bash
# USD kuru çek
php artisan exchange-rate:fetch

# Zorla güncelle (bugün için kayıt varsa bile)
php artisan exchange-rate:fetch --force
```

### Otomatik Çalıştırma (Scheduled Task)

Sistem her gün saat **09:00**'da otomatik olarak döviz kurunu çeker.

**Önemli:** Otomatik çalışması için sunucunuzda cron job kurulu olmalı:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### Windows (Laragon) için

Windows'ta otomatik çalıştırmak için:

1. **Task Scheduler** kullanın
2. Veya manuel olarak çalıştırın: `php artisan exchange-rate:fetch`

## API Kaynakları

### 1. TCMB (Birincil Kaynak)
- **URL:** https://www.tcmb.gov.tr/kurlar/today.xml
- **Format:** XML
- **Güncelleme:** Her gün sabah
- **Ücretsiz:** ✅
- **Resmi:** ✅

### 2. ExchangeRate-API (Yedek Kaynak)
- **URL:** https://api.exchangerate-api.com/v4/latest/USD
- **Format:** JSON
- **Güncelleme:** Gerçek zamanlı
- **Ücretsiz:** ✅ (Rate limit var)
- **Resmi:** ❌

## Veritabanı

Döviz kurları `exchange_rates` tablosunda saklanır:

```sql
- id
- currency (USD, EUR, GBP)
- rate_date (Tarih)
- rate (Kur değeri)
- notes (Notlar)
- created_at
- updated_at
```

## Kod Yapısı

### Service
`Modules/Core/Services/ExchangeRateApiService.php`
- API'den kur çekme
- Veritabanına kaydetme
- Hata yönetimi

### Command
`app/Console/Commands/FetchExchangeRate.php`
- CLI komutu
- Manuel çalıştırma
- Loglama

### Scheduled Task
`routes/console.php`
- Otomatik çalıştırma zamanlaması
- Hata yönetimi

## Loglama

Tüm işlemler Laravel log dosyasına kaydedilir:
- `storage/logs/laravel.log`

Başarılı çekme:
```
[INFO] Exchange rate fetched successfully
```

Hata durumunda:
```
[ERROR] Error fetching exchange rate from TCMB
[WARNING] TCMB API request failed
```

## Sorun Giderme

### API'den kur çekilemiyor

1. **İnternet bağlantısını kontrol edin**
2. **TCMB API'sine erişim:** https://www.tcmb.gov.tr/kurlar/today.xml
3. **Log dosyasını kontrol edin:** `storage/logs/laravel.log`

### Scheduled task çalışmıyor

1. **Cron job kurulu mu kontrol edin:**
   ```bash
   crontab -l
   ```

2. **Manuel test:**
   ```bash
   php artisan schedule:run
   ```

3. **Windows'ta:** Task Scheduler kullanın

### Kur değeri yanlış

1. **TCMB sitesini kontrol edin:** https://www.tcmb.gov.tr/kurlar/kurlar_tr.html
2. **Manuel güncelleme yapın:**
   ```bash
   php artisan exchange-rate:fetch --force
   ```

## Örnek Kullanım

### Backend'de Kullanım

```php
use Modules\Core\Services\CurrencyService;

// USD'den TRY'ye çevir
$tryAmount = CurrencyService::usdToTry(100); // 100 USD = ? TRY

// TRY'den USD'ye çevir
$usdAmount = CurrencyService::tryToUsd(3434); // 3434 TRY = ? USD

// Format ile birlikte
$formatted = CurrencyService::formatWithTry(100);
// ['usd' => 100, 'try' => 3434.14, 'rate' => 34.3414]
```

### Frontend'de Kullanım

```javascript
import { formatWithTry, usdToTry } from '../utils/currency.js';

// Format ile göster
const formatted = await formatWithTry(100, { primary: 'usd' });
// "$100.00 (₺3,434.14)"

// Sadece TRY değeri
const tryAmount = await usdToTry(100);
```

## Notlar

- TCMB API'si genellikle sabah saatlerinde güncellenir
- Hafta sonları ve resmi tatillerde güncelleme olmayabilir
- Sistem otomatik olarak yedek kaynağa (ExchangeRate-API) geçer
- Aynı tarih için birden fazla kayıt oluşturulmaz (güncelleme yapılır)
