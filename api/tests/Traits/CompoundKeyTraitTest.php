<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */
use App\Models\Localization;

/**
 * Class CompoundKeyTraitTest.
 */
class CompoundKeyTraitTest extends TestCase
{
    /**
     * @expectedException     LogicException
     * @expectedExceptionCode 0
     */
    public function testBootCompoundKeyTrait()
    {
        MockSinglePK::bootCompoundKeyTrait();
    }

    public function testGetQualifiedColumnName()
    {
        $localization = new Localization();

        $qualifiedColumnName = $localization->getQualifiedColumnName('region_code');

        $this->assertEquals($qualifiedColumnName, 'localizations.region_code');
    }
}

class MockSinglePK extends Localization
{
    protected $primaryKey = 'foo_id';
}
