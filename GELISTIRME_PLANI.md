# Akgün Teknik - Yedek Parça Ticareti İçin Geliştirme Planı

## 📊 Mevcut Durum Analizi

### ✅ Mevcut Özellikler
- ✅ Temel stok yönetimi (ürün, depo, hareketler)
- ✅ Satış ve satın alma siparişleri
- ✅ Müşteri ve tedarikçi yönetimi
- ✅ Basit fatura sistemi (draft, sent, paid)
- ✅ Kasa/banka takibi
- ✅ Teklif sistemi
- ✅ İade işlemleri
- ✅ Temel raporlama

### ❌ Eksik Kritik Özellikler

#### 1. **Resmi Fatura Sistemi**
- ❌ KDV hesaplama ve takibi eksik
- ❌ E-fatura/E-arşiv entegrasyonu yok
- ❌ Fatura seri/numara yönetimi eksik
- ❌ PDF fatura oluşturma yok
- ❌ Fatura şablonları yok
- ❌ Müşteri tipine göre (B2B/B2C) farklı fatura formatları yok

#### 2. **Döviz ve İthalat Yönetimi**
- ❌ Purchase Order'da döviz desteği yok
- ❌ Döviz kuru takibi yok
- ❌ Gümrük masrafları takibi yok
- ❌ İthalat vergileri (KDV, ÖTV vb.) takibi yok
- ❌ Döviz bazlı maliyet hesaplama yok

#### 3. **Fiyatlandırma ve Müşteri Grupları**
- ❌ Müşteri tipi (Toptancı/Perakende) ayrımı yok
- ❌ Farklı fiyat listeleri yok
- ❌ Müşteri bazlı özel fiyatlandırma yok
- ❌ Toplu fiyat güncelleme yok
- ❌ Kampanya/İndirim sistemi yok

#### 4. **Sipariş ve Stok Yönetimi**
- ❌ Stok rezervasyonu yok (sipariş verildiğinde stok ayrılması)
- ❌ Bekleyen siparişler takibi yok
- ❌ Sipariş durumları yetersiz (pending, processing, shipped, delivered)
- ❌ Teslimat takibi yok
- ❌ Minimum stok seviyesi uyarıları eksik

#### 5. **Ödeme ve Finans**
- ❌ Fatura ödeme durumu detaylı takibi yok
- ❌ Vade takibi ve uyarıları yok
- ❌ Çek/senet takibi yok
- ❌ Ödeme planı oluşturma yok
- ❌ Banka mutabakatı yok

#### 6. **Raporlama**
- ❌ KDV raporu yok
- ❌ Ciro raporu (toptancı/perakende ayrımı) yok
- ❌ Kar/zarar analizi eksik
- ❌ Müşteri bazlı satış analizi yok
- ❌ Ürün bazlı karlılık analizi yok
- ❌ Yaşlandırma raporu yok

#### 7. **Diğer Eksikler**
- ❌ Barkod okuyucu entegrasyonu yok
- ❌ Toplu işlemler (toplu ürün ekleme, fiyat güncelleme) yok
- ❌ Bildirim sistemi (düşük stok, vade yaklaşan faturalar) yok
- ❌ Ürün kategorileri yok
- ❌ Marka/Model yönetimi yok
- ❌ Ürün görselleri yok

---

## 🎯 Öncelikli Geliştirme Planı

### **Faz 9: Resmi Fatura ve KDV Sistemi** (Yüksek Öncelik)

#### 9.1 KDV Yönetimi
- [ ] KDV oranları tablosu (0%, 1%, 10%, 20%)
- [ ] Ürün bazlı KDV oranı atama
- [ ] Fatura oluştururken otomatik KDV hesaplama
- [ ] KDV dahil/hariç fiyatlandırma seçeneği
- [ ] KDV raporu (aylık/üç aylık)

#### 9.2 Fatura İyileştirmeleri
- [ ] Fatura seri/numara otomatik oluşturma (seri: FAT, numara: 000001)
- [ ] Fatura durumları: draft → sent → paid → cancelled
- [ ] Müşteri bilgileri faturaya otomatik ekleme
- [ ] Firma bilgileri (vergi dairesi, adres) tenant'a ekleme
- [ ] Fatura notları ve özel alanlar

#### 9.3 PDF Fatura Oluşturma
- [ ] PDF fatura şablonu (Türkçe standart format)
- [ ] Fatura görüntüleme sayfası
- [ ] Fatura indirme (PDF)
- [ ] Fatura yazdırma
- [ ] E-posta ile fatura gönderme

#### 9.4 Müşteri Tipi ve Fatura Formatı
- [ ] Müşteri tipi: B2B (Toptancı) / B2C (Perakende)
- [ ] B2B: Ticari fatura (KDV dahil, vergi no zorunlu)
- [ ] B2C: Perakende satış fişi (basit format)
- [ ] Müşteri kartına tip seçimi

---

### **Faz 10: Döviz ve İthalat Yönetimi** (Yüksek Öncelik)

#### 10.1 Döviz Desteği
- [ ] Döviz kurları tablosu (USD, EUR, GBP)
- [ ] Günlük döviz kuru girişi
- [ ] Purchase Order'da döviz seçimi
- [ ] Döviz bazlı maliyet hesaplama
- [ ] Döviz kuru geçmişi

#### 10.2 İthalat Masrafları
- [ ] Gümrük masrafları takibi
- [ ] İthalat vergileri (KDV, ÖTV) takibi
- [ ] Navlun/kargo masrafları
- [ ] Sigorta masrafları
- [ ] Toplam ithalat maliyeti hesaplama

#### 10.3 İthalat Siparişi İyileştirmeleri
- [ ] Purchase Order'a döviz alanı
- [ ] İthalat masrafları ekleme
- [ ] Toplam maliyet hesaplama (ürün + masraflar)
- [ ] İthalat fatura takibi

---

### **Faz 11: Fiyatlandırma ve Müşteri Grupları** (Orta Öncelik)

#### 11.1 Müşteri Grupları
- [ ] Müşteri tipi: Toptancı / Perakende
- [ ] Müşteri grubu oluşturma (VIP, Standart, Yeni)
- [ ] Grup bazlı indirim oranları
- [ ] Müşteri bazlı özel fiyatlandırma

#### 11.2 Fiyat Listeleri
- [ ] Toptancı fiyat listesi
- [ ] Perakende fiyat listesi
- [ ] Ürün bazlı fiyat listesi
- [ ] Miktar bazlı fiyat kademeleri (1-10: X₺, 11-50: Y₺)
- [ ] Toplu fiyat güncelleme

#### 11.3 İndirim ve Kampanyalar
- [ ] Müşteri bazlı indirim
- [ ] Ürün bazlı indirim
- [ ] Miktar bazlı indirim
- [ ] Kampanya yönetimi
- [ ] Geçici fiyat değişiklikleri

---

### **Faz 12: Gelişmiş Sipariş Yönetimi** (Orta Öncelik)

#### 12.1 Sipariş Durumları
- [ ] Yeni durumlar: pending → processing → shipped → delivered → cancelled
- [ ] Sipariş durumu geçmişi
- [ ] Durum değişikliği bildirimleri
- [ ] Sipariş takip numarası

#### 12.2 Stok Rezervasyonu
- [ ] Sipariş verildiğinde stok rezervasyonu
- [ ] Rezerve stok görünümü
- [ ] Rezervasyon iptali
- [ ] Stok yetersizliğinde bekleme listesi

#### 12.3 Teslimat Takibi
- [ ] Teslimat adresi yönetimi
- [ ] Kargo firması seçimi
- [ ] Takip numarası ekleme
- [ ] Teslimat durumu güncelleme

---

### **Faz 13: Ödeme ve Vade Takibi** (Orta Öncelik)

#### 13.1 Fatura Ödeme Takibi
- [ ] Fatura ödeme durumu: ödenmedi → kısmen ödendi → ödendi
- [ ] Kısmi ödeme kaydı
- [ ] Ödeme yöntemi (nakit, kredi kartı, havale, çek)
- [ ] Ödeme tarihi takibi

#### 13.2 Vade Takibi
- [ ] Fatura vade tarihi
- [ ] Vade yaklaşan faturalar listesi
- [ ] Vadesi geçen faturalar listesi
- [ ] Vade uyarı bildirimleri
- [ ] Yaşlandırma raporu (0-30, 31-60, 61-90, 90+ gün)

#### 13.3 Çek/Senet Takibi
- [ ] Çek/senet kaydı
- [ ] Vade tarihi takibi
- [ ] Tahsilat durumu
- [ ] Protesto takibi

---

### **Faz 14: Gelişmiş Raporlama** (Düşük Öncelik)

#### 14.1 Finansal Raporlar
- [ ] KDV raporu (aylık/üç aylık)
- [ ] Ciro raporu (toptancı/perakende ayrımı)
- [ ] Kar/zarar analizi
- [ ] Nakit akış raporu
- [ ] Alacak/borç raporu

#### 14.2 Satış Analizi
- [ ] Müşteri bazlı satış analizi
- [ ] Ürün bazlı satış analizi
- [ ] Kategori bazlı satış analizi
- [ ] Zaman bazlı trend analizi

#### 14.3 Stok Analizi
- [ ] Ürün bazlı karlılık analizi
- [ ] Stok devir hızı
- [ ] Ölü stok analizi
- [ ] ABC analizi

---

### **Faz 15: Kullanıcı Deneyimi İyileştirmeleri** (Düşük Öncelik)

#### 15.1 Ürün Yönetimi
- [ ] Ürün kategorileri
- [ ] Marka/Model yönetimi
- [ ] Ürün görselleri
- [ ] Ürün varyantları (renk, beden vb.)

#### 15.2 Toplu İşlemler
- [ ] Toplu ürün ekleme (Excel import)
- [ ] Toplu fiyat güncelleme
- [ ] Toplu stok güncelleme
- [ ] Toplu kategori atama

#### 15.3 Bildirimler
- [ ] Düşük stok uyarıları
- [ ] Vade yaklaşan faturalar
- [ ] Yeni sipariş bildirimleri
- [ ] Sistem bildirimleri

---

## 🚀 Uygulama Önceliği

### **1. Acil (Hemen Başlanmalı)**
1. **KDV Hesaplama ve Fatura İyileştirmeleri** (Faz 9.1, 9.2)
2. **PDF Fatura Oluşturma** (Faz 9.3)
3. **Müşteri Tipi (B2B/B2C)** (Faz 9.4)

### **2. Yüksek Öncelik (1-2 Hafta İçinde)**
4. **Döviz Desteği** (Faz 10.1)
5. **İthalat Masrafları** (Faz 10.2)
6. **Fiyat Listeleri** (Faz 11.2)

### **3. Orta Öncelik (1 Ay İçinde)**
7. **Sipariş Durumları** (Faz 12.1)
8. **Stok Rezervasyonu** (Faz 12.2)
9. **Vade Takibi** (Faz 13.2)

### **4. Düşük Öncelik (İlerleyen Dönem)**
10. **Gelişmiş Raporlama** (Faz 14)
11. **Toplu İşlemler** (Faz 15.2)
12. **Bildirimler** (Faz 15.3)

---

## 📝 Detaylı Özellik Açıklamaları

### **KDV Hesaplama Sistemi**

**Gereksinimler:**
- Türkiye'de yedek parça için genellikle %20 KDV
- Bazı ürünlerde %1 veya %10 KDV olabilir
- Fatura oluştururken KDV dahil/hariç seçeneği
- KDV tutarı ayrı gösterilmeli

**Teknik Detaylar:**
- `tax_rates` tablosu oluştur (0%, 1%, 10%, 20%)
- `products` tablosuna `tax_rate_id` ekle
- Fatura hesaplamalarında KDV otomatik hesaplansın
- KDV raporu için aylık toplamlar

### **PDF Fatura Oluşturma**

**Gereksinimler:**
- Türkçe standart fatura formatı
- Firma logosu ve bilgileri
- Müşteri bilgileri
- Ürün listesi (KDV dahil/hariç)
- Toplam tutarlar (ara toplam, KDV, genel toplam)
- Fatura numarası ve tarihi

**Teknik Detaylar:**
- Laravel'de PDF oluşturma için `dompdf` veya `tcpdf` kullanılabilir
- Fatura şablonu Blade template olarak
- PDF indirme ve yazdırma özellikleri

### **Döviz Yönetimi**

**Gereksinimler:**
- Yurtdışından alımda döviz cinsi seçimi (USD, EUR)
- Günlük döviz kuru girişi
- Purchase Order'da döviz ve kur bilgisi
- TL karşılığı otomatik hesaplama

**Teknik Detaylar:**
- `exchange_rates` tablosu (tarih, döviz, kur)
- `purchase_orders` tablosuna `currency`, `exchange_rate` ekle
- Döviz kuru API entegrasyonu (opsiyonel)

---

## 💡 Öneriler

1. **E-fatura Entegrasyonu**: İlerleyen dönemde e-fatura entegrasyonu eklenebilir (Uyumsoft, Logo vb.)
2. **Barkod Okuyucu**: Satış noktasında hız için barkod okuyucu entegrasyonu
3. **Mobil Uygulama**: Depo ve satış noktası için mobil uygulama
4. **E-ticaret Entegrasyonu**: Web sitesi ile entegrasyon

---

## 🎯 Sonuç

Bu plan, yedek parça ticareti yapan bir firma için gerekli tüm özellikleri kapsamaktadır. Öncelik sırasına göre uygulanması önerilir. İlk fazlar (KDV, PDF Fatura, Döviz) acil ihtiyaçlar olduğu için öncelikli olarak geliştirilmelidir.
