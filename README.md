#Garanti ve Est Sanal Pos
------------------------

Garanti Bankası ve EST destekleyen bankalar (İş Bankası, Akbank, Finansbank, Halk Bankası ve Anadolubank) için Sanal Pos sınıfları.

Şu an sadece normal API için uygulamaları bulunuyor. 3D uygulamaları şu an bulunmamaktadır.

#Kurulum
Composer ile
Aşağıdaki şekilde bir composer.json dosyası yaratıp php composer.phar install komutu ile kurulum yapılabilir. Veya var olan composer.json dosyasına eklenebilir.
```json
{
    "require": {
        "defiant/sanalpos": "dev-master"
    }
}
```

Daha sonra proje içine aşağı şekilde autoload yapılabilir.
```php
<?php
require 'vendor/autoload.php';
```

## EST (İş Bankası, Akbank, Finansbank, Halk Bankası ve Anadolubank sanal pos'ları)

Est için ```SanalPosEst``` sınıfını kullanıyoruz.
Objeyi şu parametreler ile yaratıyoruz.
- Banka: Sanal pos'u kullanılmak istenen banka (isbank, akbank, finansbank, halkbank, anadolubank)
- clientId - Banka tarafından verilen clientId
- username - Banka tarafından verilen username
- password - Banka tarafında verilen password

Daha sonra setCard metodu ile card bilgilerini giriyoruz
('kart numarası', 'son kullanma ay', 'son kullanma yıl', 'Cvv numarası')

Üçüncü adım ise sipariş bilgilerinin girilmesi
- Sipariş Numarası (her sipariş için sistemde farklı bir 'id' olması gerekiyor. Aynı id ile mükerrer siparişler hata veriyor)
- Siparişi verenin email adresi (Bazı bankalarda bu alan mecburi değil, bu gibi durumlarda geçerli herhangi bir email adresi gönderilebilir.)
- Sipariş tutarı (100 TL, 100.25 (Yüz lira 25 kuruş gibi) EST sisteminde kuruşlar için virgül veya nokta kullanılabilir (100.25'de 100,25 aynı)
- Taksit tutarı: Kaç taksit yapılacaksa o değer. Ğeşin satışlar için boş bırakılabilir.

Test modu için setMode metodu 'TEST' parametresi ile kullanılır. Bu parametre kullanılmaz ise veya başka bir değer verilirse gerçek işlem yapılır.

Son olarak ```pay``` metodu ile provizyon alınır. pay metoduna TRUE parametresi verilirse PreAuth (önotorisazyon) işlemi yapılır aksi takdirde Auth (satış) işlemi yapılır.

### Satış (Auth veya PreAuth)
Test verileri ile örnek kullanım:
```php
$est = new \SanalPos\Est\SanalPosEst('isbank', '700100000', 'ISBANKAPI', 'ISBANK07');
$est->setCard('4508034508034509', '12', '16', '000');
$est->setOrder('deneme123', 'test@test.com', '1');
$est->setMode('TEST');

$result = new \SanalPos\Est\SanalPosResponseEst($est->pay());
var_dump($result);
```

### Ön Otorizasyon Kapama (PostAuth)
PreAuth ile açılan siparişlerin kapatılmasında kullanılır. EST sisteminde setCard ve setOrder metodlarının kullanılmasına gerek yoktur.
postAuth tek parametre olarak preAuth yapılmış siparişin numarasına ihtiyaç duyar.
```php
$est = new \SanalPos\Est\SanalPosEst('isbank', '700100000', 'ISBANKAPI', 'ISBANK07');
$est->postAuth('deneme1234');
```

### İptal (Void) işlemi
İptal, bir işlemin gün sonundan önce iptal edilmesi demektir. Gün sonu işleminden sonra refund kullanılması gerekir.
cancel metodu tek parametre olarak siparişin numarasına ihtiyaç duyar.
```php
$est = new \SanalPos\Est\SanalPosEst('isbank', '700100000', 'ISBANKAPI', 'ISBANK07');
$est->cancel('deneme1234');
```

### İade (Refund)
Siparişin bir kısmının ve tamamının iptal edilmesi. İade işlemi ancak gün sonundan sonra yapılabilir.
redund metodu parametre olarak preAuth yapılmış siparişin numarasına ve opsiyonel miktara ihtiyaç duyar. Eğer opsiyonel miktar belirtilmez ise, tüm miktar iade edilir.
```php
$est = new \SanalPos\Est\SanalPosEst('isbank', '700100000', 'ISBANKAPI', 'ISBANK07');
$est->refund('deneme1234', '25') // Siparişin 25 lirasını iade et.
// veya
$est->refund('deneme1234') // Siparişin tümünü iade et.
```

### Cevaplar
Cevaplar XML şeklinde alınır \SanalPos\Est\SanalPosResponseEst nesnesi bu cevaplar için ortak bir sınıf olarak kullanılanabilir.
Üç metodu var
success: İşlem başarılı ise true aksi takdirde false döner
errors: Alınan hata mesajı döner
response: Alınan XML cevabı olduğu gibi döndürür.

## Garanti Bankası
Garanti sınıfı da yukarıdakine benzer şekilde kullanılmaktadır.
Fark olarak banka ismi gerekmez ama banka tarafından verilen bilgiler girilir. Bunlar sırasıyla;
- merchantId,
- terminalId,
- userId,
- password,
- provisionUser

Bunun dışındaki diğer işlemler yukarıdakiler ile aynıdır. Fakat Garanti bankasında her işlemden önce setCard ve setOrder metodları kullanılmalıdır. Bunun sebebi Garanti bankasının bu verileri Hash verisini hesaplamakta kullanılmasıdır.

```php
// Sınıfı initialize et
$garantiPos = new \SanalPos\Garanti\SanalPosGaranti('7000679', '30691297', 'PROVAUT', '123qweASD', 'PROVAUT');

// Kredi kartı Bilgilerini set et
$garantiPos->setCard('4282209027132016', '05', '15', '232');

// Sipariş verilerini set et
// Mecburi alanlar 'orderId', 'customer email', 'order total'
$garantiPos->setOrder('deneme23', 'test@test.com', '1');

// Test sunucusunda deneme yapmak için mode'u 'TEST' olarak ayarlıyoruz.
$garantiPos->setMode('TEST');

// pay() metodu ile ödemeyi gönderiyoruz.
$result = new \SanalPos\Garanti\SanalPosReponseGaranti($garantiPos->pay());

// Eğer sorgu onaylandı ise success metodu true döndürüyor
// aksi halde errors() metodunda hata mesajı var.
if($result->success()){
    // transaction successful
}else{
    //transaction failed
    var_dump($result->errors());
}
```

##TODO
İlk planda;
- Base sınıf içinde çeşitli kontrollerin eklenmesi.
- İptal ve refund işlemlerin eklenmesi