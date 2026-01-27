# 🚀 Akgün Teknik ERP - Nasıl Başlatılır?

Bu dosya, uygulamayı bilgisayarınızda (yeniden) başlatmak için takip etmeniz gereken en güncel adımları içerir.

---

## 🏁 En Hızlı Başlatma Yöntemi (Sadece 2 Adım)

Uygulamanın çalışması için **iki ayrı terminal** penceresi açık olmalıdır.

### 1️⃣ Terminal 1: Arka Planı (Sunucuyu) Başlat

Yeni bir terminal açın ve şu komutları sırasıyla yapıştırın:

```powershell
cd C:\Users\orhan.eymur\Desktop\akgunteknik
C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan serve
```
*Ekranda `Server running on [http://127.0.0.1:8000]` yazısını görünce bu pencereyi **açık bırakın**.*

### 2️⃣ Terminal 2: Ön Yüzü (Ekranları) Başlat

Yeni bir terminal daha açın ve şunları yapıştırın:

```powershell
cd C:\Users\orhan.eymur\Desktop\akgunteknik
npm run dev
```
*Ekranda `Local: http://localhost:5173/` yazısını görünce bu pencereyi de **açık bırakın**.*

👉 **Tarayıcıda Aç:** http://127.0.0.1:8000 adresine gidin.

---

## �️ Sorun Giderme (Sık Karşılaşılan Hatalar)

### 🔴 "Sayfa Beyaz / Açılmıyor"
*   Genellikle kodlarda yapılan bir değişiklik sonrası oluşur.
*   **Çözüm:** 2. Terminalde (npm olan) `Ctrl+C` yapıp durdurun, ardından `npm run build` yazıp Enter'a basın. İşlem bitince tekrar `npm run dev` yapın.

### 🔴 "401 Unauthorized" Hatası
*   Giriş yapmış olmanıza rağmen "Yetkisiz Erişim" hatası alırsanız.
*   **Çözüm:** Tarayıcı önbelleğini temizleyin veya çıkış yapıp tekrar giriş yapın. (Bu sorun sistem genelinde düzeltilmiştir).

### � "PHP bulunamadı" veya komut hatası
*   Eğer "php" komutu çalışmazsa, hep tam yolu kullanın:
    `C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe`

---

## ℹ️ Yönetici Giriş Bilgileri
*   **E-mail:** `admin@akgunteknik.com`
*   **Şifre:** `password`

---

**Son Güncelleme:** 27 Ocak 2026 (İade Modülü Eklendi)

