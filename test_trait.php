<?php
trait MyTrait {
    public function test() {
        echo $this->myProperty;
    }
}
class MyClass {
    use MyTrait;
    private $myProperty = 'hello';
}
$obj = new MyClass();
$obj->test();
?>
