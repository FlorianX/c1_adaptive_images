<?php
declare(strict_types=1);
namespace C1\AdaptiveImages\Tests\Unit\ViewHelpers;

use C1\AdaptiveImages\ViewHelpers\Placeholder\SvgViewHelper;
use Nimut\TestingFramework\Rendering\RenderingContextFixture;
use TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperInterface;

/**
 * Class ImageViewHelperTest
 */
class SvgViewHelperTest extends AbstractViewHelperTest
{

    /**
     * @var ViewHelperInterface
     */
    protected $utility;

    /**
     * set up
     */
    protected function setUp()
    {
        parent::setUp();
        $this->utility = new SvgViewHelper();

        $this->inject($this->utility, 'imageService', $this->mockImageService());
        $this->inject($this->utility, 'imageUtility', $this->mockImageUtility());
        $this->inject($this->utility, 'svgUtility', $this->mockSvgUtility());
    }

    /**
     * @test
     */
    public function testInitializeArguments()
    {
        $instance = $this->getAccessibleMock(SvgViewHelper::class, ['registerArgument']);
        $instance->expects($this->at(0))->method('registerArgument')->with('file', '\TYPO3\CMS\Core\Resource\FileInterface', $this->anything(), true);
        $instance->expects($this->at(1))->method('registerArgument')->with('cropVariant', 'string', $this->anything(), false, 'default');
        $instance->expects($this->at(2))->method('registerArgument')->with('content', 'string', $this->anything(), false, '');
        $instance->expects($this->at(3))->method('registerArgument')->with('embedPreview', 'boolean', $this->anything(), false, false);
        $instance->expects($this->at(4))->method('registerArgument')->with('embedPreviewWidth', 'integer', $this->anything(), false, 64);
        $instance->expects($this->at(5))->method('registerArgument')->with(
            'embedPreviewAdditionalParameters',
            'string',
            $this->anything(),
            false,
            '-quality 50 -sampling-factor 4:2:0 -strip -posterize 136 -colorspace sRGB -unsharp 0.25x0.25+8+0.065 -despeckle -noise 5'
        );
        $instance->setRenderingContext(new RenderingContextFixture());
        $instance->initializeArguments();
    }

    /**
     * @return array
     *
     *
     * array of test data for the viewHelpers render() method.
     *
     * Every entry is an array and contains:
     *
     * 1. viewHelper arguments
     * 2. expected return value from the viewHelper
     *
     */
    public function renderProvider()
    {
        return [
            'empty-svg' => [
                [
                    'file' => $this->mockFileObject([
                        'width' => '1200',
                        'height' => '768',
                        'mime_type' => 'jpg'
                    ]),
                    'cropVariant' => 'default'
                ],
                'data:image/svg+xml;base64,ABCDEFG...'
            ],
            'svg-with-preview' => [
                [
                    'file' => $this->mockFileObject([
                        'width' => '1200',
                        'height' => '768',
                        'mime_type' => 'jpg'
                    ]),
                    'embedPreview' => true
                ],
                'data:image/svg+xml;base64,ABCDEFG...with_content...'
            ],
        ];
    }

    /**
     * @test
     * @dataProvider renderProvider
     *
     * @param array $arguments
     * @param string $output
     */
    public function render($arguments, $output)
    {
        $this->utility->setArguments($arguments);
        $result = $this->utility->render();
        $this->assertEquals($output, $result);
    }

    /**
     * @test
     */
    public function createPreviewImageTag()
    {
        $previewImgTag = $this->utility->createPreviewImageTag('imageUri', 1024, 768);
        $this->assertEquals(
            '<image preserveAspectRatio="xMidYMid slice" xlink:href="imageUri" x="0" y="0" width="1024" height="768"></image>',
            $previewImgTag
        );
    }
}
