# 📟 Akgün Teknik - Terminal ve Komut Rehberi

Bu belge, projeyi yönetirken ihtiyacınız olabilecek tüm komutları, ne işe yaradıklarını ve nasıl kullanılacaklarını açıklar.

---

## 🐙 1. GitHub ve Yedekleme Komutları

Yaptığınız değişiklikleri internete (buluta) yedeklemek için kullanılır.

| Komut | Ne İşe Yarar? | Örnek Senaryo |
| :--- | :--- | :--- |
| `git status` | **Durum Kontrolü:** Hangi dosyaların değiştiğini gösterir. | "Acaba hangi dosyaları değiştirdim?" |
| `git add .` | **Paketleme:** Değişen *tüm* dosyaları gönderilmek üzere paketler. | Kodlamayı bitirdiniz, kaydetmeden önce bunu yapın. |
| `git commit -m "mesaj"` | **Etiketleme:** Pakete bir isim/açıklama yapıştırır. | `git commit -m "Barkod eklendi"` |
| `git push` | **Gönderme:** Paketi GitHub'a yükler. | Kodları internete yedeklemek için. |
| `git pull` | **Çekme:** GitHub'daki yeni kodları bilgisayara indirir. | Başka bir bilgisayarda değişiklik yaptıysanız. |

### 🚀 Günlük Yedekleme Rutini (Sırasıyla Yapın)
```powershell
git add .
git commit -m "Günlük yedek: Yapılan işlerin özeti"
git push
```

---

## ⚡ 2. Uygulama Çalıştırma Komutları

Projeyi açmak için her zaman **iki ayrı terminal** kullanıyoruz.

### Terminal 1: Arka Plan (Laravel)
```powershell
# Proje klasörüne git
cd C:\Users\orhan.eymur\Desktop\akgunteknik

# Sunucuyu başlat (Tam yol ile)
C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan serve
```

### Terminal 2: Ön Yüz (Vite/Vue)
```powershell
# Proje klasörüne git
cd C:\Users\orhan.eymur\Desktop\akgunteknik

# Ön yüzü başlat
npm run dev
```

---

## 🛠️ 3. Bakım ve Sorun Giderme Komutları

Sistem hata verirse veya "takılmış" gibi hissettirirse bunları kullanın.

### 🧹 Temizlik (Cache Silme)
Eğer yaptığınız değişiklik ekrana gelmiyorsa veya garip hatalar alıyorsanız:
```powershell
# Yapılandırma ve Önbelleği Temizle
C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan optimize:clear
```

### 🗄️ Veritabanı Güncelleme (Migrate)
Veritabanına yeni tablo eklediğinizde veya yapı değiştirdiğinizde:
```powershell
C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan migrate
```

### 🏗️ Yayına Hazırlık (Build)
Sayfa boş/beyaz geliyorsa veya kodları tamamen derlemek isterseniz:
```powershell
npm run build
```

---

## 🧑‍💻 4. Geliştirici Komutları (Yeni Özellik Eklerken)

### Yeni Model ve Dosyalar Oluşturma
Laravel'de elle dosya açmak yerine bu komutları kullanmak daha sağlıklıdır.

**Örnek: "Ariza" diye yeni bir modül/tablo yapacaksanız:**
```powershell
# Model, Migration ve Controller'ı tek seferde oluşturur (-mcr bayrağı)
C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan make:model Modules/TechnicalService/Models/Fault -m
```

### Rotaları (Linkleri) Listeleme
Hangi adresin nereye gittiğini görmek için:
```powershell
C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan route:list
```

---

## 💰 5. Döviz Kuru Komutları

### Döviz Kuru Çekme
```powershell
# USD kuru çek ve kaydet
C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan exchange-rate:fetch

# Zorla güncelle (bugün için kayıt varsa bile)
C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe artisan exchange-rate:fetch --force
```

**Not:** Sistem her gün saat 09:00'da otomatik olarak döviz kurunu çeker. Detaylar için `docs/EXCHANGE_RATE_API.md` dosyasına bakın.

---

## ℹ️ İpucu: Neden Uzun PHP Yolu Yazıyoruz?
Bilgisayarınızda birden fazla PHP sürümü olabilir. Laragon'un doğru sürümünü (8.3.28) kullandığımızdan emin olmak için `php` yerine `C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe` yazıyoruz.

Eğer her seferinde uzun yazmak istemiyorsanız, PowerShell'i açınca şu komutu bir kere yapıştırın:
```powershell
Set-Alias -Name php -Value "C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe"
```
Artık o pencere kapanana kadar sadece `php artisan ...` yazabilirsiniz.
