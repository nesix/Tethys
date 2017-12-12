<?php

namespace Tethys\Utils;

use Tethys\Databases\Record;
use Tethys\Databases\RecordFilter;

/**
 * @property string $date
 * @property bool $state
 */
abstract class Image extends Record
{

    const TYPE_ORIGINAL = 0;

    /**
     * Mime
     */
    const MIME_JPEG = 'image/jpeg';
    const MIME_PNG = 'image/png';
    const MIME_GIF = 'image/gif';

    /**
     * Варианты обработки файлов
     */
    const SIZE_ORIGINAL = 0;
    const SIZE_FIT = 1;
    const SIZE_THUMB = 2;

    /** @var ImageFile[] */
    public $files;

    /**
     * Возвращает список файлов либо, если указан тип, конкретный файл
     * @param int $type
     * @return ImageFile|ImageFile[]
     */
    public function files($type = null)
    {
        if (null === $this->files) $this->files = $this->filesFilter()->fetch(0, 0, 'type');
        return (null === $type) ? $this->files : ( isset($this->files[$type]) ? $this->files[$type] : null );
    }

    /**
     * Возвращает массив ссылок на изображения или ссылку на конкретный тип
     * @param int $type
     * @return string[]|string
     */
    public function links($type = null)
    {
        if (null === $type) {
            $ret = [];
            foreach ($this->files() as $type=>$file) $ret[$type] = $file->link;
            return $ret;
        } else {
            return $this->files($type) ? $this->files($type)->link : '';
        }
    }

    /**
     * @return RecordFilter
     */
    public function filesFilter()
    {
        $class = static::getFileClass();
        return $class::find( [ 'image_id' => $this->id ] );
    }


    /**
     * Список типов файлов изображения
     * @return array
     */
    public static function getTypes()
    {
        return [
            self::TYPE_ORIGINAL => [ 'size' => self::SIZE_ORIGINAL ],
        ];
    }

    /**
     * @param string $file
     * @param string $mime
     * @param string $suffix
     * @return static
     * @throws \Exception
     */
    public static function upload($file, $mime = self::MIME_JPEG, $suffix = '')
    {

        if (!class_exists('\Imagick')) {
            throw new ImageErrorException('Класс Imagick не существует');
        }

        /** @var \Imagick $im */
        $im = null;

        try {
            $im = new \Imagick($file);
            if (!$im) throw new \Exception('Не удалось создать объект Imagick');
        } catch (\Exception $e) {
            throw new ImageErrorException($e->getMessage());
        }

        switch ($mime) {
            case(self::MIME_GIF):
                $im->setFormat('GIF');
                $ext = '.gif';
                break;
            case(self::MIME_PNG):
                $im->setFormat('PNG');
                $ext = '.png';
                break;
            default:
                $im->setFormat('JPEG');
                $ext = '.jpg';
                break;
        }

        $image = static::make();
        $image->state = 1;
        $image->save();

        if (!$image->id) throw new ImageErrorException('Не удалось сохранить изображение');

        $imageClass = static::getFileClass();
        $files = [];

        try {

            foreach (static::getTypes() as $type=>$params) {

                $imageFile = $imageClass::makeByImage($im, $params)
                    ->obtain([
                        'image_id' => $image->id,
                        'type' => $type,
                    ])
                    ->processImage($ext);

                $imageFile->save();
                if (!$imageFile->id) throw new \Exception('Не удалось сохранить файл');

            }
        } catch (\Exception $e) {

            foreach ($files as $f) unlink($f);
            $image = null;
            throw new ImageErrorException($e->getMessage());
        }
        return $image;
    }

    /**
     * @return ImageFile|string
     */
    public static function getFileClass()
    {
        return 'Tethys\Utils\ImageFile';
    }

    public static function getFields()
    {
        return array_merge(parent::getFields(), [
            'date' => [ self::FIELD_DATETIME ],
            'state' => [ self::FIELD_BOOL ],
        ]);
    }

}