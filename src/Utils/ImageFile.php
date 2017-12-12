<?php

namespace Tethys\Utils;

use Tethys\Databases\Record;

/**
 * @property $image_id
 * @property $type
 * @property $link
 */
abstract class ImageFile extends Record
{

    /**
     * @var \Imagick
     */
    protected $im;

    /**
     * @var \Imagick
     */
    protected $source_im;

    /**
     * @var array
     */
    protected $params;

    /**
     * @return string
     */
    abstract public function getBaseUploadDir();

    /**
     * @param string $ext
     * @return $this
     */
    public function processImage($ext)
    {
        $filename = $this->makeNewFilename($ext);
        $this->im = clone $this->source_im;
        $this->beforeModify();
        $size = $this->params['size'] ?? Image::SIZE_ORIGINAL;
        $workers = $this->getProcessWorkers();
        if (isset($workers[$size]) && is_callable($workers[$size])) {
            call_user_func($workers[$size]);
        }
        $this->afterModify();
        $this->im->writeImage($filename);
        return $this;
    }

    /**
     * @return array
     */
    public function getProcessWorkers()
    {
        return [
            Image::SIZE_THUMB => [ $this, 'processThumbImage' ],
            Image::SIZE_FIT => [ $this, 'processFitImage' ],
        ];
    }

    /**
     * @param $ext
     * @return string
     * @throws \Exception
     */
    protected function makeNewFilename($ext)
    {
        $folder = $this->getBaseUploadDir();
        $limit = 1000;
        while (true && (0 < $limit--)) {
            $baseName = substr(md5(rand().time()), 0, 12).$ext;
            $sub = substr($baseName, 0, 2).'/'.substr($baseName, 2, 2);
            $this->link = $sub.'/'.$baseName;
            $filename = $folder.$this->link;
            if (!file_exists($filename)) {
                $dir = dirname($filename);
                if (!file_exists($dir)) mkdir($dir, 0755, true);
                if (touch($filename)) return $filename;
            }
        }
        throw new ImageErrorException('Не удалось получить уникальное имя файла картинки');
    }

    protected function processFitImage()
    {
        $width = (int)($this->params['width'] ?? 0);
        $height = (int)($this->params['height'] ?? 0);
        if (!($width && $height)) throw new ImageErrorException('Не указана ширина и высота картинки');
        $width = min($width, $this->im->getImageWidth());
        $height = min($height, $this->im->getImageHeight());
        $this->im->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1, true);
    }

    protected function processThumbImage()
    {
        $width = (int)($this->params['width'] ?? 0);
        $height = (int)($this->params['height'] ?? 0);
        if (!($width && $height)) throw new ImageErrorException('Не указана ширина и высота картинки');
        $this->im->cropThumbnailImage($width, $height);
    }

    /**
     * @return array
     */
    public static function getFields()
    {
        return array_merge(parent::getFields(), [
            'image_id' => [ static::FIELD_INT ],
            'type' => [ static::FIELD_INT ],
            'link' => [ static::FIELD_STRING ],
        ]);
    }

    /**
     * @param Image[]|array $images
     * @param int $type
     */
    public static function fillFiles(&$images, $type = null)
    {
        if (!$images) return;

        $filesFilter = static::find(array_merge(
            [ 'image_id' => array_keys($images) ],
            ( null !== $type ? [ 'type' => $type ] : [ ] )
        ));

        /** @var static $item */
        foreach ($filesFilter->fetch() as $item) {
            if (null === $images[$item->image_id]->files) $images[$item->image_id]->files = [];
            $images[$item->image_id]->files[$item->type] = $item;
        }

    }

    /**
     * @throws ImageErrorException
     */
    protected function beforeModify()
    {
        if (isset($this->params['beforeAction'])) {
            if (!is_callable($this->params['beforeAction'])) {
                throw new ImageErrorException('Bad before action');
            }
            call_user_func_array($this->params['beforeAction'], [
                $this,
                $this->im,
                $this->params
            ]);
        }
        try {
            if (isset($this->params['quality']) && is_numeric($this->params['quality'])) {
                if (!$this->im->setImageCompressionQuality((int)$this->params['quality'])) throw new \Exception('Не удалось изменить качество');
            }
            if (isset($params['crop'])) {
                $this->preCrop($this->im, $this->params['crop']);
            }
        } catch (\Exception $e) {
            throw new ImageErrorException($e->getMessage());
        }
    }

    /**
     * @throws ImageErrorException
     */
    protected function afterModify()
    {
        if (isset($this->params['afterAction'])) {
            if (!is_callable($this->params['afterAction'])) {
                throw new ImageErrorException('Bad after action');
            }
            call_user_func_array($this->params['afterAction'], [
                $this,
                $this->im,
                $this->params
            ]);
        }
    }


    /**
     * @param \Imagick $im
     * @param int|array $bounds
     * @return bool
     */
    protected function preCrop($im, $bounds)
    {

        if (is_numeric($bounds)) {
            $left = (int)$bounds;
            $top = (int)$bounds;
            $right = (int)$bounds;
            $bottom = (int)$bounds;
        } else {
            $left = isset($bounds['left']) ? (int)$bounds['left'] : (isset($bounds['horizontal']) ? (int)$bounds['horizontal'] : 0);
            $top = isset($bounds['top']) ? (int)$bounds['top'] : (isset($bounds['vertical']) ? (int)$bounds['vertical'] : 0);
            $right = isset($bounds['right']) ? (int)$bounds['right'] : (isset($bounds['horizontal']) ? (int)$bounds['horizontal'] : 0);
            $bottom = isset($bounds['bottom']) ? (int)$bounds['bottom'] : (isset($bounds['vertical']) ? (int)$bounds['vertical'] : 0);
        }

        $size = $im->getImageGeometry();

        if ($left || $top || $right || $bottom) {

            $width = $size['width'] - ($left + $right);
            $height = $size['height'] - ($top + $bottom);

            $im->cropImage($width, $height, $left, $top);

        }

    }

    /**
     * @param \Imagick $im
     * @param array $params
     * @return static
     */
    public static function makeByImage($im, $params)
    {
        $file = static::make();
        $file->source_im = $im;
        $file->params = $params;
        return $file;
    }

}