<?php
namespace Tests\GameEngine;

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../GameEngine/Form.php';

class FormTest extends TestCase {
    public function testForm() {
        $form = new \Form([]);
        $this->assertEquals(0, $form->returnErrors());
    }

    public function testFormWithErrors() {
        $data = [
            'errorarray' => ['field1' => 'error1'],
            'valuearray' => ['field1' => 'val1']
        ];
        $form = new \Form($data);
        $this->assertEquals(1, $form->returnErrors());
        $this->assertEquals('error1', $form->getError('field1'));
        $this->assertEquals('val1', $form->getValue('field1'));
    }
}
