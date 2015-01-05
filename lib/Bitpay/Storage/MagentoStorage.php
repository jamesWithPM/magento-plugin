<?php
/**
 * @license Copyright 2011-2014 BitPay Inc., MIT License
 * @see https://github.com/bitpay/magento-plugin/blob/master/LICENSE
 */

namespace Bitpay\Storage;

/**
 * This is part of the magento plugin. This is responsible for saving and loading
 * keys for magento.
 */
class MagentoStorage implements StorageInterface
{
    /**
     * @var array
     */
    protected $_keys;

    /**
     * @inheritdoc
     */
    public function persist(\Bitpay\KeyInterface $key)
    {
        $this->_keys[$key->getId()] = $key;

        $data          = serialize($key);
        $encryptedData = \Mage::helper('core')->encrypt($data);
        $config        = new \Mage_Core_Model_Config();

        if (true === isset($config) && false === empty($config)) {
            $config->saveConfig($key->getId(), $encryptedData);
        } else {
            \Mage::helper('bitpay')->debugData('[ERROR] In file lib/Bitpay/Storage/MagentoStorage.php, class MagentoStorage::persist - Could not instantiate a \Mage_Core_Model_Config object.');
            throw new Exception('[ERROR] In file lib/Bitpay/Storage/MagentoStorage.php, class MagentoStorage::persist - Could not instantiate a \Mage_Core_Model_Config object.');
        }
    }

    /**
     * @inheritdoc
     */
    public function load($id)
    {
        if (true === isset($id) && true === isset($this->_keys[$id])) {
            return $this->_keys[$id];
        }

        $entity = \Mage::getStoreConfig($id);

        /**
         * Not in database
         */
        if (false === isset($entity) || true === empty($entity)) {
            \Mage::helper('bitpay')->debugData('[INFO] Call to MagentoStorage::load($id) with the id of ' . $id . ' did not return the store config parameter because it was not found in the database.');
            return false;
        }

        $decodedEntity = unserialize(\Mage::helper('core')->decrypt($entity));

        if (false === isset($decodedEntity) || true === empty($decodedEntity)) {
            \Mage::helper('bitpay')->debugData('[INFO] Call to MagentoStorage::load($id) with the id of ' . $id . ' could not decrypt & unserialize the entity ' . $entity . '.');
            return false;
        }

        \Mage::helper('bitpay')->debugData('[INFO] Call to MagentoStorage::load($id) with the id of ' . $id . ' successfully decrypted & unserialized the entity ' . $entity . '.');

        return $decodedEntity;
    }
}
