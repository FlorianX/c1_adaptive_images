<?php

namespace C1\ImageRenderer\Utility;

use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class RatioBoxUtility
{
    
    /** @var PageRenderer */
    protected $pageRenderer;

    /**
     * @var array $ratioBoxClassNames
     */
    protected $ratioBoxClassNames;

    public function __construct($pageRenderer = null) {
        if (!$pageRenderer) {
            $this->pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        } else {
            $this->pageRenderer = $pageRenderer;
        }
    }

    /**
     * @var array $ratioBoxBase
     */
    protected $ratioBoxBase;

    /**
     * Setter for $this->ratioBoxBase
     *
     * @param string $ratioBoxBase
     */
    public function setRatioBoxBase($ratioBoxBase = 'ratio-box') {
        $this->ratioBoxBase = $ratioBoxBase;
    }

    /**
     * Removes unwanted characters from css ClassNames
     *
     * @param $class
     * @returns string
     */
    public function sanitizeCssClassName($class)
    {
        $class = \strtolower($class);
        // remove all characters not allowed in HTML class names
        $regex = '/[^\\x{002D}\\x{0030}-\\x{0039}\\x{0041}-\\x{005A}\\x{005F}\\x{0061}-\\x{007A}\\x{00A1}-\\x{FFFF}]/u';
        $class = \preg_replace($regex, '', $class);
        $class = \preg_replace("/[\s_]/", "-", $class);
        return $class;
    }

    /**
     *
     * Returns a class name for the ratio box (for intrinsic ratio css)
     *
     * Because ratio can be a float and dots are not allowed inside css class names dots in $ratio are replaced with
     * 'dot'. After that the resulting string is also filtered to make sure it does only contain valid chars to use in
     * css class names.
     *
     * @param int|float $ratio
     * @param string|null $mq
     * @return string
     */
    public function getRatioClassForCropVariant($ratio, $mq = null)
    {
        $ratioBoxClass = null;
        $ratioBoxBase = $this->ratioBoxBase;

        if($mq) {
            $ratioBoxClass = sprintf(
                '%s--%s-%s',
                $ratioBoxBase,
                $this->sanitizeCssClassName($mq),
                \preg_replace('/\./i', 'dot', $ratio)
            );
        } else {
            $ratioBoxClass = sprintf(
                '%s--%s',
                $ratioBoxBase,
                \preg_replace('/\./i', 'dot', $ratio)
            );
        }


        return $this->sanitizeCssClassName($ratioBoxClass);
    }

    /**
     *
     * Get the default style for the ratio box
     *
     * @param int|float $ratio
     * @param string $mq
     * @return string
     */
    public function getRatioBoxStyle($ratio, $mq = null)
    {
        if ($mq) {
            return sprintf(
                '@media %s{.%s{padding-bottom:%s%%}}',
                $mq,
                $this->getRatioClassForCropVariant($ratio, $mq),
                $ratio
            );
        } else {
            return sprintf(
                '.%s{padding-bottom:%s%%}',
                $this->getRatioClassForCropVariant($ratio, null),
                $ratio
            );
        }
    }

    /**
     *
     * Add inline css to the header for generated ratio-box class names
     *
     * @param string $class
     * @param string $css
     * @param boolean $compress
     */
    public function addStyleToHeader($class, $css, $compress = true) {
        $this->pageRenderer->addCssInlineBlock($class, $css, $compress);
    }

    /**
     * return ratio box classNames
     *
     * @param array $cropVariants
     *
     * @return array
     */
    public function getRatioBoxClassNames($cropVariants) {

        $this->ratioBoxClassNames[] = $this->ratioBoxBase;

        foreach (array_reverse($cropVariants) as $cropVariantKey => $cropVariantConfig) {
            $mq = $cropVariantConfig['media'] ?? null;
            $className = $this->getRatioClassForCropVariant($cropVariantConfig['ratio'], $mq);
            $this->ratioBoxClassNames[] = $className;
            $css = $this->getRatioBoxStyle($cropVariantConfig['ratio'], $mq);
            $this->addStyleToHeader($className, $css, 1);
        };

        return $this->ratioBoxClassNames;
    }

}