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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\View;

use Magento\View\Element\BlockFactory;

class DataSourcePool
{
    /**
     * @var \Magento\View\Element\BlockFactory
     */
    protected $blockFactory;

    /**
     * @var array
     */
    protected $dataSources = array();

    /**
     * @var array
     */
    protected $assignments = array();

    /**
     * @param \Magento\View\Element\BlockFactory $blockFactory
     */
    public function __construct(BlockFactory $blockFactory)
    {
        $this->blockFactory = $blockFactory;
    }

    /**
     * @param string $name
     * @param string $class
     * @return object
     * @throws \Exception
     */
    public function add($name, $class)
    {
        if (!isset($this->dataSources[$name])) {

            if (!class_exists($class)) {
                throw new \Exception(__('Invalid Data Source class name: ' . $class));
            }

            $data = $this->blockFactory->createBlock($class);

            $this->dataSources[$name] = $data;
        }

        return $this->dataSources[$name];
    }

    /**
     * @param null $name
     * @return array|object|null
     */
    public function get($name = null)
    {
        if (!isset($name)) {
            return $this->dataSources;
        }

        return isset($this->dataSources[$name]) ? $this->dataSources[$name] : null;
    }

    /**
     * @param $dataName
     * @param $namespace
     * @param $alias
     */
    public function assign($dataName, $namespace, $alias)
    {
        $alias = $alias ?: $dataName;
        $data = $this->get($dataName);

        $this->assignments[$namespace][$alias] = $data;
    }

    /**
     * @param $namespace
     * @return array
     */
    public function getNamespaceData($namespace)
    {
        return isset($this->assignments[$namespace]) ? $this->assignments[$namespace] : array();
    }
}
