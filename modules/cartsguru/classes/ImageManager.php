<?php
/**
 * Carts Guru
 *
 * @author    LINKT IT
 * @copyright Copyright (c) LINKT IT 2016
 * @license   Commercial license
 */

class CartsGuruImageManager
{
    /**
     * Max execution time for image generation
     *
     * @var int
     */
    const MAX_EXECUTION_TIME = 7200;
    const THUMB_SIZE = 120;
    public static function sortImageByWidth($i, $j)
    {
        return $i['width'] > $j['width'];
    }

    public static function findProductThumbnailName()
    {
        $imageTypes = ImageType::getImagesTypes('products');
        $filtered = array();
        foreach ($imageTypes as $imageType) {
            if ($imageType['height'] >= self::THUMB_SIZE && $imageType['width'] >= self::THUMB_SIZE) {
                $filtered[] = $imageType;
            }
        }
        if (empty($filtered)) {
            return null;
        }
        usort($filtered, array(
            "CartsGuruImageManager",
            "sortImageByWidth"
        ));
        return $filtered[0]['name'];
    }

    public static function initProductThumbnail()
    {
        $imageType = self::findProductThumbnailName();
        if ($imageType == null) {
            self::insertCartsGuruImageType();
            Configuration::updateValue('CARTSG_IMAGE_GENERATE', 0);
            Configuration::updateValue('CARTSG_IMAGE_TYPE', 'cartsguru');
        } else {
            Configuration::updateValue('CARTSG_IMAGE_GENERATE', 1);
            Configuration::updateValue('CARTSG_IMAGE_TYPE', $imageType);
        }
    }

    public static function insertCartsGuruImageType()
    {
        $image_type = new ImageType();
        $image_type->name = 'cartsguru';
        $image_type->width = 120;
        $image_type->height = 120;
        $image_type->products = 1;
        $image_type->categories = 0;
        $image_type->manufacturers = 0;
        $image_type->suppliers = 0;
        $image_type->scenes = 0;
        $image_type->stores = 0;
        if (version_compare(_PS_VERSION_, '1.5.0', '<')) {
            $image_type->store = 0;
        }
        $image_type->add();
    }

    public static function deleteCartsGuruImageType()
    {
        $image_type = self::getCartsGuruImageType();
        if ($image_type) {
            self::deleteProductImageCG();

            $imageType = new ImageType((int)$image_type['id_image_type']);
            if (Validate::isLoadedObject($imageType)) {
                $imageType->delete();
            }
        }
        return true;
    }

    public static function getCartsGuruImageType()
    {
        return ImageType::getByNameNType('cartsguru', 'products');
    }

    public static function deleteProductImageCG()
    {
        $image_type = self::getCartsGuruImageType();
        if (!$image_type) {
            return;
        }
        $productsImages = Image::getAllImages();
        foreach ($productsImages as $image) {
            $imageObj = new Image($image['id_image']);
            $imageObj->id_product = $image['id_product'];
            if (version_compare(_PS_VERSION_, '1.5.0', '<')) {
                $img_folder = _PS_PROD_IMG_DIR_ . $image['id_product'] . '-' . $image['id_image'];
            } else {
                $img_folder = _PS_PROD_IMG_DIR_ . $imageObj->getImgFolder();
            }
            if (file_exists($img_folder)) {
                $toDel = scandir($img_folder);
                foreach ($toDel as $d) {
                    if (preg_match('/^[0-9]+\-' . $image_type['name'] . '\.jpg$/', $d)) {
                        if (file_exists($img_folder . $d)) {
                            unlink($img_folder . $d);
                        }
                    }
                }
            }
        }
    }

    public static function generateProductImageCG()
    {
        $start_time = time();
        ini_set('max_execution_time', self::MAX_EXECUTION_TIME); // ini_set may be disabled, we need the real value
        $max_execution_time = (int)ini_get('max_execution_time');
        $errors = array();
        $img_cg_type = self::getCartsGuruImageType();
        foreach (Image::getAllImages() as $image) {
            $imageObj = new Image($image['id_image']);
            $existing_img = '';
            if (version_compare(_PS_VERSION_, '1.5.0', '<')) {
                $existing_img = _PS_PROD_IMG_DIR_ . $image['id_product'] . '-' . $image['id_image'] . '.jpg';
            } else {
                $existing_img = _PS_PROD_IMG_DIR_ . $imageObj->getExistingImgPath() . '.jpg';
            }
            if (file_exists($existing_img) && filesize($existing_img)) {
                if (version_compare(_PS_VERSION_, '1.5.0', '<')) {
                    $to_img_file_cg = _PS_PROD_IMG_DIR_ . $image['id_product'] . '-' . $image['id_image'] . '-' . Tools::stripslashes($img_cg_type['name']) . '.jpg';
                } else {
                    $to_img_file_cg = _PS_PROD_IMG_DIR_ . $imageObj->getExistingImgPath() . '-' . Tools::stripslashes($img_cg_type['name']) . '.jpg';
                }
                if (!file_exists($to_img_file_cg)) {
                    if (version_compare(_PS_VERSION_, '1.5.0', '<')) {
                        if (!imageResize($existing_img, $to_img_file_cg, (int)$img_cg_type['width'], (int)$img_cg_type['height'])) {
                            $errors[] = sprintf('Original image is corrupt (%s) for product ID %2$d or bad permission on folder', $existing_img, (int)$imageObj->id_product);
                        }
                    } else {
                        if (!ImageManager::resize($existing_img, $to_img_file_cg, (int)$img_cg_type['width'], (int)$img_cg_type['height'])) {
                            $errors[] = sprintf(Tools::displayError('Original image is corrupt (%s) for product ID %2$d or bad permission on folder'), $existing_img, (int)$imageObj->id_product);
                        }
                    }
                }
            } else {
                $errors[] = sprintf('Original image is missing or empty (%1$s) for product ID %2$d', $existing_img, (int)$imageObj->id_product);
            }
            if (time() - $start_time > $max_execution_time - 4) {
                $errors[] = 'timeout';
                return $errors;
            }
        }
        if (!empty($errors)) {
            return $errors;
        }
        Configuration::updateValue('CARTSG_IMAGE_GENERATE', 1);
        return true;
    }
}
