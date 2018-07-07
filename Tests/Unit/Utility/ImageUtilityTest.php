<?php
declare(strict_types=1);
namespace C1\AdaptiveImages\Tests\Unit\Utility;

use C1\AdaptiveImages\Utility\ImageUtility;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class ImageUtilityTest
 * @package C1\AdaptiveImages\Tests\Unit\Utility
 */
class ImageUtilityTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{

    /**
     * @var MockObject|objectManager
     */
    protected $objectManagerMock;

    /**
     * @var array $settingsMock
     */
    protected $settingsMock = [
        'debug' => '0',
        'srcsetWidths' => '240,360,480,660,840,1024,1280,1440,1680,1920',
    ];

    /**
     * @var array optionsMock
     */
    protected $optionsMock = [
        'additionalAttributes' => null,
        'data' => null,
        'class' => 'image-embed-item',
        'dir' => null,
        'id' => null,
        'lang' => null,
        'style' => null,
        'title' => '',
        'accesskey' => null,
        'tabindex' => null,
        'onclick' => null,
        'alt' => '',
        'file' => null,
        'width' => 2560,
        'height' => 1475,
        'cropVariant' => 'default',
        'renderMode' => 'fluidtemplate',
        'cropVariants' => [
            'mobile' => [
                'srcsetWidths' => '320,640',
                'media' => '(max-width:767px)'
            ]
        ],
        'debugImgProperties' => 'TRUE'
    ];

    protected function setUp()
    {
        parent::setUp();
        $this->objectManagerMock = $this->createMock(ObjectManager::class);
    }

    /** @test */
    public function setOriginalFileSetsFile()
    {
        /** @var File $fileMock */
        $fileMock = $this->createMock(File::class);
        $utility = new ImageUtility($this->optionsMock, $this->settingsMock, $this->objectManagerMock);
        $utility->setOriginalFile($fileMock);

        Assert::assertAttributeInstanceOf('TYPO3\CMS\Core\Resource\File', 'originalFile', $utility);
    }


    /** @test */
    public function getCropVariantsReturnsCropVariants()
    {
        /** @var MockObject|ImageUtility $mock */
        $mock = $this->getMockBuilder(ImageUtility::class)
            ->setConstructorArgs([$this->optionsMock, $this->settingsMock, $this->objectManagerMock])
            ->setMethods(['processSrcsetImages'])
            ->getMock();

        $candidates = [
            'mobile' => [
                '320' => [
                    'url' => 'file-320.jpg',
                    'width' => 320,
                    'ratio' => 0.75
                ],
                '640' => [
                    'url' => 'file-640.jpg',
                    'width' => 640,
                    'ratio' => 0.75
                ],
            ],
            'default' => [
                '600' => [
                    'url' => 'file-600.jpg',
                    'width' => 600,
                    'ratio' => 0.5
                ],
                '992' => [
                    'url' => 'file-992.jpg',
                    'width' => 992,
                    'ratio' => 0.5
                ],
            ],
        ];

        $expectation = [
            'mobile' => [
                'srcsetWidths' => '320,640',
                'media' => '(max-width:767px)',
                'candidates' => $candidates['mobile'],
                'srcset' => 'file-320.jpg 320w,file-640.jpg 640w',
                'ratio' => 0.75
            ],
            'default' => [
                'srcsetWidths' => '600,992',
                'candidates' => $candidates['default'],
                'srcset' => 'file-600.jpg 600w,file-992.jpg 992w',
                'ratio' => 0.5
            ]
        ];

        $mock->expects($this->at(0))
            ->method('processSrcsetImages')
            ->will($this->returnValue($candidates['mobile']));

        $mock->expects($this->at(1))
            ->method('processSrcsetImages')
            ->will($this->returnValue($candidates['default']));


        $this->assertEquals(
            $expectation,
            $mock->getCropVariants()
        );
    }
}
