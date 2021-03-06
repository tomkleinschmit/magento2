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
 * @package     Magento_Adminhtml
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Controller\Adminhtml;

/**
 * @magentoAppArea adminhtml
 */
class CategoryTest extends \Magento\Backend\Utility\Controller
{
    /**
     * @magentoDataFixture Magento/Core/_files/store.php
     * @magentoDbIsolation enabled
     * @dataProvider saveActionDataProvider
     * @param array $inputData
     * @param array $defaultAttributes
     * @param array $attributesSaved
     */
    public function testSaveAction($inputData, $defaultAttributes, $attributesSaved = array())
    {
        /** @var $store \Magento\Core\Model\Store */
        $store = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Core\Model\Store');
        $store->load('fixturestore', 'code');
        $storeId = $store->getId();

        $this->getRequest()->setPost($inputData);
        $this->getRequest()->setParam('store', $storeId);
        $this->getRequest()->setParam('id', 2);
        $this->dispatch('backend/catalog/category/save');

        $this->assertSessionMessages(
            $this->equalTo(array('You saved the category.')), \Magento\Core\Model\Message::SUCCESS
        );

        /** @var $category \Magento\Catalog\Model\Category */
        $category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Catalog\Model\Category');
        $category->setStoreId($storeId);
        $category->load(2);

        $errors = array();
        foreach ($attributesSaved as $attribute => $value) {
            $actualValue = $category->getData($attribute);
            if ($value !== $actualValue) {
                $errors[] = "value for '$attribute' attribute must be '$value', but '$actualValue' is found instead";
            }
        }

        foreach ($defaultAttributes as $attribute => $exists) {
            if ($exists !== $category->getExistsStoreValueFlag($attribute)) {
                if ($exists) {
                    $errors[] = "custom value for '$attribute' attribute is not found";
                } else {
                    $errors[] = "custom value for '$attribute' attribute is found, but default one must be used";
                }
            }
        }

        $this->assertEmpty($errors, "\n" . join("\n", $errors));
    }

    /**
     * @param array $postData
     * @dataProvider categoryCreatedFromProductCreationPageDataProvider
     * @magentoDbIsolation enabled
     */
    public function testSaveActionFromProductCreationPage($postData)
    {
        $this->getRequest()->setPost($postData);

        $this->dispatch('backend/catalog/category/save');
        $body = $this->getResponse()->getBody();

        if (empty($postData['return_session_messages_only'])) {
            $this->assertRegExp(
                '~<script type="text/javascript">parent\.updateContent\("[^"]+/backend/catalog/category/edit/'
                    . 'id/\d+/key/[0-9a-f]+/", {}, true\);</script>~',
                $body
            );
        } else {
            $result = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Helper\Data')
                ->jsonDecode($body);
            $this->assertArrayHasKey('messages', $result);
            $this->assertFalse($result['error']);
            $category = $result['category'];
            $this->assertEquals('Category Created From Product Creation Page', $category['name']);
            $this->assertEquals(1, $category['is_active']);
            $this->assertEquals(0, $category['include_in_menu']);
            $this->assertEquals(2, $category['parent_id']);
            $this->assertNull($category['available_sort_by']);
            $this->assertNull($category['default_sort_by']);
        }
    }

    /**
     * @static
     * @return array
     */
    public static function categoryCreatedFromProductCreationPageDataProvider()
    {
        /* Keep in sync with new-category-dialog.js */
        $postData = array(
            'general' => array (
                'name' => 'Category Created From Product Creation Page',
                'is_active' => 1,
                'include_in_menu' => 0,
            ),
            'parent' => 2,
            'use_config' => array('available_sort_by', 'default_sort_by'),
        );

        return array(
            array($postData),
            array($postData + array('return_session_messages_only' => 1)),
        );
    }

    public function testSuggestCategoriesActionDefaultCategoryFound()
    {
        $this->getRequest()->setParam('label_part', 'Default');
        $this->dispatch('backend/catalog/category/suggestCategories');
        $this->assertEquals(
            '[{"id":"2","children":[],"is_active":"1","label":"Default Category"}]',
            $this->getResponse()->getBody()
        );
    }

    public function testSuggestCategoriesActionNoSuggestions()
    {
        $this->getRequest()->setParam('label_part', strrev('Default'));
        $this->dispatch('backend/catalog/category/suggestCategories');
        $this->assertEquals('[]', $this->getResponse()->getBody());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public function saveActionDataProvider()
    {
        return array(
            'default values' => array(
                array(
                    'general'  => array(
                        'id'        => '2',
                        'path'      => '1/2',
                        'url_key'   => 'default-category',
                        'is_anchor' => '0',
                    ),
                    'use_default' => array(
                        0  => 'name',
                        1  => 'is_active',
                        2  => 'thumbnail',
                        3  => 'description',
                        4  => 'image',
                        5  => 'meta_title',
                        6  => 'meta_keywords',
                        7  => 'meta_description',
                        8  => 'include_in_menu',
                        9  => 'display_mode',
                        10 => 'landing_page',
                        11 => 'available_sort_by',
                        12 => 'default_sort_by',
                        13 => 'filter_price_range',
                        14 => 'custom_apply_to_products',
                        15 => 'custom_design',
                        16 => 'custom_design_from',
                        17 => 'custom_design_to',
                        18 => 'page_layout',
                        19 => 'custom_layout_update',
                    ),
                ),
                array(
                    'name'                     => false,
                    'default_sort_by'          => false,
                    'display_mode'             => false,
                    'meta_title'               => false,
                    'custom_design'            => false,
                    'page_layout'              => false,
                    'is_active'                => false,
                    'include_in_menu'          => false,
                    'landing_page'             => false,
                    'is_anchor'                => false,
                    'custom_apply_to_products' => false,
                    'available_sort_by'        => false,
                    'description'              => false,
                    'meta_keywords'            => false,
                    'meta_description'         => false,
                    'custom_layout_update'     => false,
                    'custom_design_from'       => false,
                    'custom_design_to'         => false,
                    'filter_price_range'       => false,
                ),
            ),
            'custom values'  => array(
                array(
                    'general' => array(
                        'id'                       => '2',
                        'path'                     => '1/2',
                        'name'                     => 'Custom Name',
                        'is_active'                => '0',
                        'description'              => 'Custom Description',
                        'meta_title'               => 'Custom Title',
                        'meta_keywords'            => 'Custom keywords',
                        'meta_description'         => 'Custom meta description',
                        'include_in_menu'          => '0',
                        'url_key'                  => 'default-category',
                        'display_mode'             => 'PRODUCTS',
                        'landing_page'             => '1',
                        'is_anchor'                => '1',
                        'custom_apply_to_products' => '0',
                        'custom_design'            => 'magento_blank',
                        'custom_design_from'       => '',
                        'custom_design_to'         => '',
                        'page_layout'              => '',
                        'custom_layout_update'     => '',
                    ),
                    'use_config' => array(
                        0 => 'available_sort_by',
                        1 => 'default_sort_by',
                        2 => 'filter_price_range',
                    ),
                ),
                array(
                    'name'                     => true,
                    'default_sort_by'          => true,
                    'display_mode'             => true,
                    'meta_title'               => true,
                    'custom_design'            => true,
                    'page_layout'              => true,
                    'is_active'                => true,
                    'include_in_menu'          => true,
                    'landing_page'             => true,
                    'custom_apply_to_products' => true,
                    'available_sort_by'        => true,
                    'description'              => true,
                    'meta_keywords'            => true,
                    'meta_description'         => true,
                    'custom_layout_update'     => true,
                    'custom_design_from'       => true,
                    'custom_design_to'         => true,
                    'filter_price_range'       => true,
                ),
                array(
                    'name'                     => 'Custom Name',
                    'default_sort_by'          => NULL,
                    'display_mode'             => 'PRODUCTS',
                    'meta_title'               => 'Custom Title',
                    'custom_design'            => 'magento_blank',
                    'page_layout'              => NULL,
                    'is_active'                => '0',
                    'include_in_menu'          => '0',
                    'landing_page'             => '1',
                    'custom_apply_to_products' => '0',
                    'available_sort_by'        => NULL,
                    'description'              => 'Custom Description',
                    'meta_keywords'            => 'Custom keywords',
                    'meta_description'         => 'Custom meta description',
                    'custom_layout_update'     => NULL,
                    'custom_design_from'       => NULL,
                    'custom_design_to'         => NULL,
                    'filter_price_range'       => NULL,
                ),
            ),
        );
    }

    public function testSaveActionCategoryWithDangerRequest()
    {
        $this->getRequest()->setPost(array(
            'general' => array(
                'path' => '1',
                'name' => 'test',
                'is_active' => '1',
                'entity_id' => 1500,
                'include_in_menu' => '1',
                'available_sort_by' => 'name',
                'default_sort_by' => 'name',
            ),
        ));
        $this->dispatch('backend/catalog/category/save');
        $this->assertSessionMessages(
            $this->equalTo(array('Unable to save the category')), \Magento\Core\Model\Message::ERROR
        );
    }
}
