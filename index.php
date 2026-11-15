<?php
// Proje bir alt klasore konduysa (ornek: XAMPP htdocs/uyanisinsesi) ziyaretciyi public/ klasorune yonlendirir.
// Canli sitede document root dogrudan public/ olmalidir; bu dosya orada kullanilmaz.
header('Location: public/', true, 302);
exit;
