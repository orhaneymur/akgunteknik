# 🧪 Test Senaryoları - Akgün Teknik ERP

Bu dosya, uygulamanın tüm özelliklerini test etmek için hazırlanmış manuel test senaryolarını içerir.

## 📋 Test Öncesi Hazırlık

1. **Backend'i Başlat:**
   ```powershell
   cd C:\Users\orhan.eymur\Documents\GitHub\akgunteknik
   C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan serve
   ```

2. **Frontend'i Başlat:**
   ```powershell
   cd C:\Users\orhan.eymur\Documents\GitHub\akgunteknik
   npm run dev
   ```

3. **Giriş Yap:**
   - Email: `admin@akgunteknik.com`
   - Şifre: `password`

---

## ✅ Faz 9: KDV ve Fatura Sistemi

### Test 9.1: KDV Oranı Oluşturma
1. **API Test:** `POST /api/core/tax-rates`
   ```json
   {
     "name": "KDV %20",
     "rate": 20.00,
     "is_active": true
   }
   ```
2. **Beklenen:** KDV oranı başarıyla oluşturulmalı
3. **Doğrulama:** `GET /api/core/tax-rates` ile liste kontrol edilmeli

### Test 9.2: Ürüne KDV Oranı Atama
1. **API Test:** `PUT /api/inventory/products/{id}`
   ```json
   {
     "tax_rate_id": 1
   }
   ```
2. **Beklenen:** Ürünün KDV oranı güncellenmeli
3. **Doğrulama:** `GET /api/inventory/products/{id}` ile kontrol edilmeli

### Test 9.3: Fatura Oluşturma (Siparişten)
1. **Önce Sipariş Oluştur:** `POST /api/sales/orders`
2. **Fatura Oluştur:** `POST /api/finance/invoices/from-source`
   ```json
   {
     "source_type": "order",
     "source_id": 1
   }
   ```
3. **Beklenen:** 
   - Fatura numarası otomatik oluşturulmalı (FAT-YYYYMMDD-XXXX veya PER-YYYYMMDD-XXXX)
   - KDV hesaplaması doğru olmalı
   - Müşteri tipine göre seri seçilmeli (B2B = FAT, B2C = PER)

### Test 9.4: PDF Fatura Görüntüleme
1. **API Test:** `GET /api/finance/invoices/{id}/pdf`
2. **Beklenen:** PDF dosyası döndürülmeli
3. **Doğrulama:** Tarayıcıda PDF görüntülenebilmeli

---

## ✅ Faz 10: Döviz ve İthalat Masrafları

### Test 10.1: Döviz Kuru Ekleme
1. **API Test:** `POST /api/core/exchange-rates`
   ```json
   {
     "currency": "USD",
     "rate": 30.50,
     "date": "2026-01-30"
   }
   ```
2. **Beklenen:** Döviz kuru kaydedilmeli

### Test 10.2: En Son Döviz Kuru Alma
1. **API Test:** `GET /api/core/exchange-rates/latest/USD`
2. **Beklenen:** En son USD kuru döndürülmeli

### Test 10.3: Döviz ile Purchase Order Oluşturma
1. **API Test:** `POST /api/inventory/purchase-orders`
   ```json
   {
     "supplier_id": 1,
     "warehouse_id": 1,
     "currency": "USD",
     "exchange_rate": 30.50,
     "items": [
       {
         "product_id": 1,
         "quantity": 10,
         "unit_cost": 100
       }
     ]
   }
   ```
2. **Beklenen:**
   - `total_amount` = 1000 USD
   - `total_amount_tl` = 30500 TRY
   - `exchange_rate` = 30.50

### Test 10.4: İthalat Masrafı Ekleme
1. **API Test:** `POST /api/inventory/purchase-orders/{id}/import-costs`
   ```json
   {
     "type": "customs",
     "description": "Gümrük vergisi",
     "amount": 500,
     "currency": "USD",
     "exchange_rate": 30.50
   }
   ```
2. **Beklenen:** İthalat masrafı kaydedilmeli ve TL karşılığı hesaplanmalı

---

## ✅ Faz 11: Müşteri Grupları ve Fiyat Listeleri

### Test 11.1: Müşteri Grubu Oluşturma
1. **API Test:** `POST /api/customers/customer-groups`
   ```json
   {
     "name": "VIP Müşteriler",
     "code": "VIP",
     "discount_percentage": 15.00
   }
   ```
2. **Beklenen:** Müşteri grubu oluşturulmalı

### Test 11.2: Müşteriye Grup Atama
1. **API Test:** `PUT /api/customers/customers/{id}`
   ```json
   {
     "customer_group_id": 1
   }
   ```
2. **Beklenen:** Müşteri gruba atanmalı

### Test 11.3: Fiyat Listesi Oluşturma
1. **API Test:** `POST /api/inventory/price-lists`
   ```json
   {
     "name": "Toptancı Fiyat Listesi",
     "code": "WHOLESALE",
     "type": "wholesale",
     "is_default": true
   }
   ```
2. **Beklenen:** Fiyat listesi oluşturulmalı

### Test 11.4: Ürüne Fiyat Ekleme (Fiyat Listesine)
1. **API Test:** `POST /api/inventory/price-lists/{id}/products`
   ```json
   {
     "product_id": 1,
     "price": 90.00,
     "min_quantity": 1,
     "max_quantity": 10
   }
   ```
2. **Beklenen:** Ürün fiyatı fiyat listesine eklenmeli

### Test 11.5: Otomatik Fiyatlandırma (Sipariş Oluştururken)
1. **Müşteriye VIP grubu atanmalı**
2. **Sipariş Oluştur:** `POST /api/sales/orders`
   ```json
   {
     "customer_id": 1,
     "items": [
       {
         "product_id": 1,
         "quantity": 5
       }
     ]
   }
   ```
3. **Beklenen:**
   - Fiyat listesinden fiyat alınmalı
   - Müşteri grubu indirimi uygulanmalı (%15)
   - `unit_price` otomatik hesaplanmalı

---

## ✅ Faz 12: Gelişmiş Sipariş Yönetimi

### Test 12.1: Sipariş Oluşturma (Stok Rezervasyonu ile)
1. **Stok Ekle:** Ürüne stok eklenmeli
2. **Sipariş Oluştur:** `POST /api/sales/orders`
   ```json
   {
     "customer_id": 1,
     "items": [
       {
         "product_id": 1,
         "quantity": 5
       }
     ]
   }
   ```
3. **Beklenen:**
   - Sipariş numarası otomatik oluşturulmalı (ORD-YYYYMMDD-XXXX)
   - Stok rezerve edilmeli (düşülmemeli)
   - Status = "pending" olmalı
   - Status history kaydedilmeli

### Test 12.2: Sipariş Durumu Güncelleme
1. **Processing'e Geçir:** `POST /api/sales/orders/{id}/status`
   ```json
   {
     "status": "processing"
   }
   ```
2. **Shipped'e Geçir:**
   ```json
   {
     "status": "shipped",
     "carrier": "Yurtiçi Kargo",
     "tracking_number": "YT123456789"
   }
   ```
3. **Beklenen:**
   - Status güncellenmeli
   - Stok düşülmeli (shipped olduğunda)
   - Rezervasyon "fulfilled" olmalı
   - Status history kaydedilmeli

### Test 12.3: Sipariş İptal Etme
1. **Cancel:** `POST /api/sales/orders/{id}/status`
   ```json
   {
     "status": "cancelled"
   }
   ```
2. **Beklenen:**
   - Rezervasyon serbest bırakılmalı
   - Status = "cancelled" olmalı

---

## ✅ Faz 13: Ödeme ve Vade Takibi

### Test 13.1: Fatura için Ödeme Kaydetme
1. **Fatura Oluştur** (Test 9.3'ten)
2. **Ödeme Kaydet:** `POST /api/finance/payments`
   ```json
   {
     "payable_type": "invoice",
     "payable_id": 1,
     "amount": 500.00,
     "payment_date": "2026-01-30",
     "payment_method": "cash"
   }
   ```
3. **Beklenen:**
   - Ödeme kaydedilmeli
   - Faturanın `paid_amount` artmalı
   - Faturanın `remaining_amount` azalmalı
   - Müşteri bakiyesi azalmalı

### Test 13.2: Kısmi Ödeme
1. **1000 TL'lik fatura için 300 TL ödeme yap**
2. **Beklenen:**
   - `paid_amount` = 300
   - `remaining_amount` = 700
   - Müşteri bakiyesi 300 TL azalmalı

### Test 13.3: Ödeme İptal Etme
1. **Ödeme İptal:** `POST /api/finance/payments/{id}/cancel`
2. **Beklenen:**
   - Ödeme status = "cancelled" olmalı
   - Fatura değerleri geri alınmalı
   - Müşteri bakiyesi geri artmalı

### Test 13.4: Vade Geçmiş Faturalar
1. **API Test:** `GET /api/finance/due-dates/overdue`
2. **Beklenen:** Vade tarihi geçmiş ve ödenmemiş faturalar listelenmeli

### Test 13.5: Yaklaşan Vadeler
1. **API Test:** `GET /api/finance/due-dates/due-soon?days=7`
2. **Beklenen:** 7 gün içinde vadesi gelen faturalar listelenmeli

### Test 13.6: Müşteri Ödeme Özeti
1. **API Test:** `GET /api/finance/due-dates/customers/{customerId}/summary`
2. **Beklenen:**
   - Toplam borç
   - Vade geçmiş tutar
   - Ödenmemiş faturalar ve siparişler

### Test 13.7: Vade Raporu
1. **API Test:** `GET /api/finance/due-dates/report`
2. **Beklenen:**
   - Vade geçmiş: sayı ve toplam tutar
   - Bu hafta: sayı ve toplam tutar
   - Bu ay: sayı ve toplam tutar

---

## 🔍 Genel Kontroller

### Veri İzolasyonu
- ✅ Farklı tenant'lar birbirinin verilerini görmemeli
- ✅ Tüm API endpoint'leri tenant_id kontrolü yapmalı

### Yetkilendirme
- ✅ Staff: Günlük işlemler (ürün, müşteri, sipariş)
- ✅ Manager: Finans ve kritik işlemler (fatura, ödeme, stok transferi)

### Hata Yönetimi
- ✅ Geçersiz veriler için 422 hatası
- ✅ Yetkisiz erişim için 403 hatası
- ✅ Bulunamayan kayıtlar için 404 hatası

---

## 📝 Test Sonuçları

Test tarihi: _______________
Test eden: _______________

| Test No | Test Adı | Durum | Notlar |
|---------|----------|--------|--------|
| 9.1 | KDV Oranı Oluşturma | ⬜ | |
| 9.2 | Ürüne KDV Atama | ⬜ | |
| 9.3 | Fatura Oluşturma | ⬜ | |
| 9.4 | PDF Fatura | ⬜ | |
| 10.1 | Döviz Kuru Ekleme | ⬜ | |
| 10.2 | En Son Kur | ⬜ | |
| 10.3 | Döviz PO | ⬜ | |
| 10.4 | İthalat Masrafı | ⬜ | |
| 11.1 | Müşteri Grubu | ⬜ | |
| 11.2 | Grup Atama | ⬜ | |
| 11.3 | Fiyat Listesi | ⬜ | |
| 11.4 | Ürün Fiyatı | ⬜ | |
| 11.5 | Otomatik Fiyat | ⬜ | |
| 12.1 | Sipariş + Rezervasyon | ⬜ | |
| 12.2 | Durum Güncelleme | ⬜ | |
| 12.3 | Sipariş İptal | ⬜ | |
| 13.1 | Ödeme Kaydetme | ⬜ | |
| 13.2 | Kısmi Ödeme | ⬜ | |
| 13.3 | Ödeme İptal | ⬜ | |
| 13.4 | Vade Geçmiş | ⬜ | |
| 13.5 | Yaklaşan Vadeler | ⬜ | |
| 13.6 | Müşteri Özeti | ⬜ | |
| 13.7 | Vade Raporu | ⬜ | |

---

**Son Güncelleme:** 30 Ocak 2026
