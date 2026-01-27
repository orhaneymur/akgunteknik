# Akgün Teknik - Proje Rehberi ve Teknik Dokümantasyon

Bu belge, **Akgün Teknik SaaS Projesi** için hazırlanan kapsamlı rehberdir. Projenin gelişimi, teknik altyapısı, sorun giderme adımları ve dosya yapıları burada detaylandırılmıştır.

---

## 🏗️ 1. Yapılanlar Listesi (Tamamlanan Aşamalar)

Proje, yönetilebilir parçalara (Modüller) ayrılarak geliştirilmiştir. Şu ana kadar tamamlanan özellikler:

### ✅ Faz 1: Çekirdek ve Stok Yönetimi (Inventory)
- **Depo Yönetimi:** Şube ve depo altyapısı kuruldu.
- **Ürün Yönetimi:**
    - Ürün ekleme, silme, düzenleme, stok takibi.
    - **Otomatik Stok Kodu (SKU):** Kod boş bırakılırsa sistem otomatik atar.
    - **Esnek Fiyatlandırma:** Alış/Satış fiyatı girmek zorunlu değildir.
- **Stok Hareketleri:** Giriş, çıkış, transfer işlemlerinin veritabanı kaydı.

### ✅ Faz 2 - 5: Satış, Müşteri ve Tedarik Zinciri
- **Satış Modülü:** Müşteriye ürün satışı, stok güncelleme, sipariş oluşturma.
- **Müşteri Modülü:** Müşteri/Cari kartları oluşturma, bakiye takibi.
- **Tedarikçi & Satın Alma:** Tedarikçi kartları ve mal kabul (Alış Siparişi) işlemleri.

### ✅ Faz 6 - 9: Finans, Fatura ve Teklifler
- **Nakit Akışı (Finans):** Gider kalemleri (Kira, Maaş vb.) ve Kasa/Banka takibi.
- **Faturalar:** Satış sonrası otomatik fatura oluşturma.
- **Teklif Modülü:** Müşteriye teklif verme ve onaylanan teklifi siparişe çevirme.

### ✅ Faz 10 - 13: Gelişmiş Özellikler
- **Stok Transferi:** Depolar arası ürün transferi.
- **İade & Geri Alım:**
    - **Satış İadesi:** Müşteriden gelen iadeleri yönetme ve stok artışı.
    - **Alış İadesi:** Tedarikçiye mal iadesi ve stok düşüşü.
- **Gelişmiş Raporlama:**
    - **Dashboard:** Anlık ciro, gider ve kar durumu.
    - **Satış Raporu:** Tarih bazlı satış analizi.
    - **Stok Devir Raporu:** Kritik stok seviyeleri ve en değerli ürünler.
- **Cari Hesap Ekstreleri:** Müşteri borç/alacak dökümü (Ekstre).

---

## 🔧 2. Teknik Destek (Sorun Giderme Rehberi)

Geliştirme veya kullanım sırasında karşılaşabileceğiniz olası sorunlar ve çözümleri:

### 🔴 Sorun: "Sayfa açılmıyor" veya "Siteye ulaşılamıyor"
**Kontrol Edin:**
1.  Terminal açık mı?
2.  `php artisan serve` komutu çalışıyor mu?
**Çözüm:** Terminalden `C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan serve` komutunu tekrar çalıştırın.

### 🔴 Sorun: "Vite build failed" hatası alıyorum
**Sebep:** Genellikle JavaScript/Vue dosyalarında bir yazım hatası (örn. kapanmayan etiket) olduğunda oluşur.
**Çözüm:**
1.  Hata mesajını okuyun, hangi dosyada olduğu yazar.
2.  Dosyayı düzeltin.
3.  `npm run build` komutunu tekrar çalıştırın.

### 🔴 Sorun: "Table not found" veya veritabanı hataları
**Sebep:** Yeni bir modül eklendiğinde veritabanı tabloları oluşmamış olabilir.
**Çözüm:** Terminalden şu komutu çalıştırın:
`C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan migrate`

---

## 📂 3. Proje Yapısı ve Modüller (Ne, Nerede?)

Proje **Modüler Yapı** kullanır. Kodlar `Modules/` klasörü altındadır.

### 📦 A. Modules/Inventory (Stok Yönetimi)
Bu modül, ürünlerin ve deponun kalbidir.
- **Models/Product.php:** Ürün kartı (İsim, fiyat, stok adedi).
- **Models/InventoryMovement.php:** Stok tarihçesi (Kim, ne zaman, kaç tane aldı?).
- **Models/InventoryTransfer.php:** Depolar arası transfer işlemi.
- **Http/Controllers/ProductController.php:** Ürün ekleme/silme işlerini yöneten beyin.

### 💰 B. Modules/Sales (Satış)
Satış operasyonlarını yönetir.
- **Models/Order.php:** Satış siparişi ana kaydı (Tarih, Müşteri, Toplam Tutar).
- **Models/OrderItem.php:** Siparişin içindeki satırlar (Hangi üründen kaç tane?).
- **Models/Quote.php:** Müşteriye verilen teklifler.

### 👥 C. Modules/Customer (Müşteri & Cari)
Müşteri verilerini tutar.
- **Models/Customer.php:** Müşteri bilgileri (Ad, Soyad, Bakiye).
- **Database/Migrations/...create_customers_table.php:** Veritabanındaki müşteri tablosu.

### 💵 D. Modules/Finance (Banka & Kasa)
Parasal işlemleri yönetir.
- **Models/Expense.php:** Şirket giderleri.
- **Models/Safe.php:** Kasa ve Banka hesapları.
- **Models/Transaction.php:** Para giriş-çıkış hareketleri.

### 📊 E. Modules/Core (Çekirdek & Raporlama)
Sistemin temeli ve ortak işler.
- **Http/Controllers/ReportController.php:** Tüm rapor verilerini (Ciro, Stok Değeri) hesaplayan kodlar.
- **Models/User.php:** Sisteme giriş yapan personel/yöneticiler.

### 🖥️ F. resources/js (Arayüz / Frontend)
Ekranlarda gördüğünüz tasarımlar buradadır.
- **pages/dashboard/Dashboard.vue:** Ana giriş ekranı.
- **pages/inventory/ProductList.vue:** Ürün listesi sayfası.
- **pages/reports/ReportsDashboard.vue:** Raporlar ekranı.
- **layouts/Sidebar.vue:** Sol taraftaki menü çubuğu.
- **router.js:** Hangi linke tıklayınca hangi sayfaya gidileceğini belirleyen trafik polisi.

---

## 🚀 4. İleri Seviye Geliştirme Notları

- **Yeni Modül Ekleme:** `Modules/` altına yeni klasör açın, `Routes/api.php` dosyasını oluşturup ana `bootstrap/providers.php` dosyasına servisi tanıtın.
- **Veritabanı Değişikliği:** Asla doğrudan veritabanına elle müdahale etmeyin. Her zaman `make:migration` komutu ile bir "göç dosyası" oluşturup `artisan migrate` komutunu kullanın. Bu sayede yapılan değişiklikler kaybolmaz.

Bu rehber, projenizin yaşayan bir dokümanıdır. Yeni özellikler eklendikçe güncelleyebilirsiniz. Akgün Teknik'e hayırlı olsun! 🎉
