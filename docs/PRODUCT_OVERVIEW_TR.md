# Fycbit Platform Tanitimi

Fycbit; spot ve vadeli islem deneyimi, coklu varlik cuzdanlari, fiat islemleri,
P2P pazar yeri, staking, kampanya ve icerik yonetimini tek bir platform
altyapisinda birlestirmeyi hedefleyen moduler bir dijital varlik sistemidir.

Bu depo, platformun kaynak kodu ve mimari inceleme tabanidir. Guvenlik, custody,
uyumluluk ve yuk testleri tamamlanmadan gercek fonlarla kullanima uygun oldugu
iddia edilmez.

## Platform Bilesenleri

### Kullanici Uygulamasi

- Kayit, giris, parola sifirlama ve e-posta dogrulama
- Profil, bildirim, aktivite gecmisi ve dil/para birimi tercihleri
- Google 2FA ve telefon dogrulama akislari
- KYC belge yukleme ve dogrulama durumlari
- Cuzdan ozeti, bakiye gorunumu ve islem gecmisi
- Para yatirma, cekme ve hesaplar arasi fon transferi
- Referral ve davet gecmisi
- Kullanici API erisimi ve IP beyaz liste ayarlari

### Spot Islem Altyapisi

- Alis ve satis emir defteri
- Limit ve piyasa emirleri
- Stop-limit emirleri
- Acik emirler, emir iptali ve islem gecmisi
- Parite, fiyat, hacim ve piyasa istatistikleri
- Grafik ve mum verisi tabloları
- Islem ucreti ve parite bazli hassasiyet ayarlari
- Bot emirleri ve piyasa botu kuyruklari icin altyapi

### Vadeli Islemler

`FutureTrade` modulu ve web arayuzunde vadeli islem sayfalari bulunur. Modul;
vadeli emir, pozisyon ve stop-limit is akislari icin bir taban saglar. Gercek
borsa davranisi, risk motoru, likidasyon ve margin hesaplari bagimsiz olarak
test edilmeden uretim ozelligi kabul edilmemelidir.

### Demo Trade

- Gercek fon kullanmadan platform akisini deneyimleme
- Demo cuzdan ve demo piyasa sayfalari
- Coin/parite bazinda demo ozelligini acma veya kapatma
- Kullanici egitimi ve arayuz testleri icin kontrollu ortam

### Cuzdan ve Blockchain Katmani

- Coklu coin ve network tanimlari
- Deposit adresi olusturma ve adres gecmisi
- Withdrawal on-islem ve onay akislari
- Network fee ve confirmation alanlari
- EVM, Solana ve Tron tabanli is akislari icin kod tabani
- Webhook/notifier endpointleri
- Block processor ve bildirim kayitlari
- Wallet-service icinde Prisma tabanli veri erisimi

Wallet katmani gercek varlik custody'si icin denetlenmis degildir. Mainnet private
key, mnemonic veya signing key public depoya ve tarayiciya konulamaz.

### Fiat Para Islemleri

- Banka ve kullanici banka hesabi tanimlari
- Fiat deposit ve withdrawal is akislari
- Kur ve ucret hesaplama alanlari
- Yatirma/cekme gecmisi
- Odeme saglayici entegrasyonlari icin genisletilebilir yapi

Odeme entegrasyonlari test anahtarlari ve ilgili mevzuat kontrolleri olmadan
etkinlestirilmemelidir.

### P2P ve Gift Card

- P2P ilan, teklif ve islem sayfalari
- Gift card olusturma, satin alma, gonderme ve redeem akislari
- Gift card kategori, tema ve listeleme yapisi
- Kullanici ilanlari ve siparis ekranlari

### Staking

- Staking teklifleri
- Yatirim olusturma ve iptal
- Kazanc ve odeme gecmisi
- Teklif detaylari ve istatistikler
- Zamanlanmis getiri islemleri icin console command altyapisi

Staking getirileri finansal vaat olarak sunulmadan once urun, hukuk ve risk
incelemesinden gecmelidir.

### ICO Launchpad

- Token/phase yapisi
- Satin alma ve odeme akislari
- Launchpad form ve yonetim ekranlari
- ICO withdrawal listeleri

### Icerik ve Destek

- Blog ve haber modulu
- Duyuru, banner, ozellik ve sosyal medya alanlari
- SSS ve Knowledge Base
- Destek talepleri ve sohbet ekranlari
- Ozel sayfalar ve dinamik menuler
- Coklu dil kaynaklari

### Yonetim Paneli

- Kullanici, rol ve izin yonetimi
- Coin, parite, network ve fee yonetimi
- Cuzdan, deposit, withdrawal ve transaction raporlari
- KYC, banka, fiat ve odeme ayarlari
- Tema, SEO, dil ve landing page ayarlari
- Modul durumlari ve feature kontrolleri
- Sistem aktiviteleri ve admin login kayitlari
- Queue/Horizon tabanli operasyonel isler

## Teknik Mimari

| Katman | Teknoloji | Sorumluluk |
| --- | --- | --- |
| Backend | Laravel/PHP | API, kimlik, emirler, raporlar, kuyruklar |
| Web | Next.js/React | Kullanici ve yonetim arayuzu |
| Wallet service | Node.js/TypeScript/Prisma | Blockchain ve wallet is akislari |
| Veritabani | MySQL | Kullanici, emir, cuzdan ve ayar verileri |
| Cache/Kuyruk | Redis | Cache, background jobs ve event isleme |
| Realtime | Websocket/Pusher uyumlu | Market, chat ve bildirim akislari |

## API ve Entegrasyon Yetenekleri

- Public market price, orderbook, trade ve chart endpointleri
- Kimlik dogrulanmis kullanici API endpointleri
- Kullanici API key ve IP whitelist yapisi
- Wallet webhook ve notifier endpointleri
- SMTP, sosyal giris, captcha, odeme ve blockchain saglayicilari icin
  yapilandirma noktalari

## Operasyonel Yetenekler

- Laravel migration tabani
- Redis queue ve Horizon supervisor modeli
- Market bot, trade processor, deposit, withdrawal ve mail kuyruklari
- Zamanlanmis console command'lari
- Log, bildirim ve activity kayitlari
- Yedekleme/geri yukleme proseduru icin dokumantasyon

## Kimler Icin?

- Exchange mimarisi arastiran gelistirme ekipleri
- Testnet tabanli dijital varlik prototipleri
- Moduler trading, wallet ve P2P urunu gelistiren ekipler
- Laravel, Next.js ve Node.js ile cok servisli finansal uygulama inceleyenler

## Sinirlar ve Guvenlik Uyarisi

- Production-ready veya audit edilmis degildir.
- Custody garantisi vermez.
- KYC/AML, KVKK/GDPR, lisanslama ve bolgesel mevzuat ayri inceleme ister.
- `NEXT_PUBLIC_*` alanlari gizli degildir.
- Opsiyonel moduller ve ucuncu taraf kodlari lisans incelemesi gerektirir.
- Gercek fon kullanmadan once clean-room kurulum, test, secret scan, dependency
  audit, restore testi ve bagimsiz guvenlik denetimi zorunludur.

Bu yazilim finansal tavsiye degildir.

