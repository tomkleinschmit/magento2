<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Backend
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Backend\Block\Widget\Grid\Column\Renderer;

/**
 * Backend Grid Renderer
 */
class Longtext extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Core string
     *
     * @var \Magento\Filter\FilterManager
     */
    protected $filter;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Filter\FilterManager $filter
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Filter\FilterManager $filter,
        array $data = array()
    ) {
        $this->filter = $filter;
        parent::__construct($context, $data);
    }

    /**
     * Render contents as a long text
     *
     * Text will be truncated as specified in string_limit, truncate or 250 by default
     * Also it can be html-escaped and nl2br()
     *
     * @param \Magento\Object $row
     * @return string
     */
    public function render(\Magento\Object $row)
    {
        $truncateLength = 250;
        // stringLength() is for legacy purposes
        if ($this->getColumn()->getStringLimit()) {
            $truncateLength = $this->getColumn()->getStringLimit();
        }
        if ($this->getColumn()->getTruncate()) {
            $truncateLength = $this->getColumn()->getTruncate();
        }
        $text = $this->filter->truncate(parent::_getValue($row), array('length' => $truncateLength));
        if ($this->getColumn()->getEscape()) {
            $text = $this->escapeHtml($text);
        }
        if ($this->getColumn()->getNl2br()) {
            $text = nl2br($text);
        }
        return $text;
    }
}
