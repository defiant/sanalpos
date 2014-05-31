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
## Garanti Bankası
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

## EST (İş Bankası, Akbank, Finansbank, Halk Bankası ve Anadolubank)
EST içinde benzer bir şekilde ödeme gönderiliyor.

Est için SanalPosEst sınıfını kullanıyoruz.
Diğerinden farklı olarak ilk parametrede hangi banka olduğunu belirtiyoruz.
Bunun dışında kullanımı benzer şekilde
```php
$est = new \SanalPos\Est\SanalPosEst('isbank', '700100000', 'ISBANKAPI', 'ISBANK07');
$est->setCard('4508034508034509', '12', '16', '000');
$est->setOrder('deneme123', 'test@test.com', '1');
$est->setMode('TEST');

$result = new \SanalPos\Est\SanalPosResponseEst($est->pay());
var_dump($result);
```

##TODO
İlk planda;
- Base sınıf içinde çeşitli kontrollerin eklenmesi.
- İptal ve refund işlemlerin eklenmesi