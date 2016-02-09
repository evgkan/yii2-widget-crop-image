<?php

namespace evgkan\cropimage;

use Yii;
use yii\helpers\BaseHtml;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * evgkan
 * 09.02.2016
 * used jquery.imgareaselect-0.9.10
 *
 * Class CropImage
 *
 * Значение атрибута модели - путь к картинке или массив с относительными координатами кадрирования
 * При сабмите возвращает путь к исходному изображению и относительные координаты кадрирования в виде массива:
 * [
 *      'imgSrc' => <path>,
 *      'x1' => <left 0..1>,
 *      'y1' => <top 0..1>,
 *      'x2' => <right 0..1>,
 *      'y2' => <bottom 0..1>
 * ]
 *
 * Атрибут модели при сохранении в/чтении из БД необходимо сериализовать/десериализовать
 *
 * В модели доступ к атрибуту осуществляется через метод getCropImage():
 *
 * public function getPhoto(){
 *     return CropImage::getCropImage($this->photo);
 * }
 *
 * Данный метод(если картинка в кеше отсутствует) обрезает исходную картинку в соответствии с данными кадрирования,
 * кэширует новую картинку в заданной директории и возвращает путь.
 */
class CropImage extends \mihaildev\elfinder\InputFile
{


    const THUMBS_PATH = 'thumbs';//относительно @webroot

    public $language = 'ru';
    public $controller = 'elfinder';
    public $filter = 'image';
    public $template = '{preview}{inputs}{button}'; //preview и inputs - свои
    public $buttonName = 'Выбрать фото';

    public $imgMaxWidth = '100%';
    public $imgMaxHeight = '500px';

    public function run()
    {
        $data = $this->model{$this->attribute};

        //определяем картинку в зависимости от типа входных данных стринг/массив
        $imgSrc = '';
        $coords = 'null';
        if(is_array($data)){
            if(array_key_exists('imgSrc', $data)) $imgSrc = $data['imgSrc'];
            if( array_key_exists('x1', $data) &&
                array_key_exists('y1', $data) &&
                array_key_exists('x2', $data) &&
                array_key_exists('y2', $data) )
            {
                $x1=$data['x1'];$y1=$data['y1'];$x2=$data['x2'];$y2=$data['y2'];
                $coords = "{'x1':$x1, 'y1':$y1, 'x2':$x2, 'y2':$y2}";
            }
        } elseif(!empty($data)) {
            $imgSrc = $data;
        }

        //рендерим блок с картинкой
        $previewId = $this->id . '_preview_img';
        $previewImgClass = 'form-img-preview';
        if (empty($imgSrc)) { $preview = "<div id='$previewId'></div>";}
        else { $preview = Html::tag('div', Html::img($imgSrc, ['class'=>$previewImgClass]), ['id' => $previewId]); }
        $this->template = strtr($this->template, ['{preview}'=>$preview]);

        //рендерим инпуты
        $inputs = Html::activeTextInput($this->model, $this->attribute.'[imgSrc]', $this->options);
        $inputs .= Html::activeTextInput($this->model, $this->attribute.'[x1]', ['type'=>'hidden']);
        $inputs .= Html::activeTextInput($this->model, $this->attribute.'[y1]', ['type'=>'hidden']);
        $inputs .= Html::activeTextInput($this->model, $this->attribute.'[x2]', ['type'=>'hidden']);
        $inputs .= Html::activeTextInput($this->model, $this->attribute.'[y2]', ['type'=>'hidden']);
        $this->template = strtr($this->template, ['{inputs}'=>$inputs]);
        parent::run();

        //css и js
        $view = $this->getView();
        //подключаем ресурсы
        CropImageAsset::register($view);
        //стили для отображения картинки
        $view->registerCss(".$previewImgClass {max-width:$this->imgMaxWidth; max-height:$this->imgMaxHeight}");
        //подключаем файловый менеджер сервера
        $view->registerJs("mihaildev.elFinder.register(" . Json::encode($this->options['id']) . ",
            function(file, id){
                UnInitImageAreaSelect();
                \$('#' + id).val(file.url);
                \$('#$previewId').html('<img class=\"$previewImgClass\" src=\"'+file.url+'\">');
                $('#$previewId .$previewImgClass').load(function(){ InitImageAreaSelect(null) });
                return true;}); ");
        //подключаем инициализацию imageAreaSelect с координатами или без
        $inputId = BaseHtml::getInputId($this->model, $this->attribute);
        $view->registerJs("
            function InitImageAreaSelect(coords){
                if(coords==null) {coords={'x1':0, 'y1':0, 'x2':1, 'y2':1};}
                var ias = $('#$previewId .$previewImgClass');
                width = ias.width(); height = ias.height();
                //console.log(width, height);
                ias.imgAreaSelect({
                    handles: true,
                    x1: coords.x1*width,
                    y1: coords.y1*height,
                    x2: coords.x2*width,
                    y2: coords.y2*height,
                    onSelectEnd: function(img,sel) {
                        x1=sel.x1/width; y1=sel.y1/height; x2=sel.x2/width; y2=sel.y2/height;
                        updateInputs(x1,y1,x2,y2);
                    }
                });
                var obj = $('#$previewId .$previewImgClass');
                updateInputs(coords.x1, coords.y1, coords.x2, coords.y2);
                function updateInputs(x1,y1,x2,y2){
                    //console.log(x1, y1, x2, y2);
                    if(x1==x2 || y1==y2){ x1 = y1 = 0; x2 = y2 = 1; }
                    $('#$inputId-x1').val(x1);
	                $('#$inputId-y1').val(y1);
	                $('#$inputId-x2').val(x2);
	                $('#$inputId-y2').val(y2);
                }
            }
            InitImageAreaSelect($coords);
            function UnInitImageAreaSelect(){
                $('#$previewId .$previewImgClass').imgAreaSelect({ remove: true });
            }
            ");

    }


    public static function getCropImage($data){
        if(
            !is_array($data) ||
            !array_key_exists('imgSrc',$data) ||
            !array_key_exists('x1',$data) ||
            !array_key_exists('y1',$data) ||
            !array_key_exists('x2',$data) ||
            !array_key_exists('y2',$data)
        ) return null;
        $imgSrc = trim($data['imgSrc'], '/');
        $imgSrvPath = Yii::getAlias('@webroot').'/'.$imgSrc;
        if(!is_file($imgSrc)) return null;
        $x1=$data['x1'];$y1=$data['y1'];$x2=$data['x2'];$y2=$data['y2'];
        //генерация путей и имени файла
        $thumbFileName = md5( $imgSrc.$x1.$y1.$x2.$y2 ) . '_crop.jpg';
        $thumbWebPath = '/'.self::THUMBS_PATH.'/'.$thumbFileName;
        $thumbSrvPath = Yii::getAlias('@webroot').$thumbWebPath;
        //если тумба уже есть, отдаем
        if(is_file($thumbSrvPath)) return $thumbWebPath;
        //иначе создаем
        list($imgWidth, $imgHeight) = getimagesize($imgSrvPath);
        $x1 = round($x1*$imgWidth); $y1 = round($y1*$imgHeight);
        $x2 = round($x2*$imgWidth); $y2 = round($y2*$imgHeight);
        $thumbWidth = $x2-$x1; $thumbHeight = $y2-$y1;
        $newThumb = imagecreatetruecolor($thumbWidth, $thumbHeight);
        //хак для автоопределения типа изображения
        $curImage = imagecreatefromstring(file_get_contents($imgSrvPath));
        imagecopyresampled($newThumb, $curImage, 0, 0, $x1, $y1, $thumbWidth, $thumbHeight, $thumbWidth, $thumbHeight);
	    imagejpeg($newThumb, $thumbSrvPath, 95);
        return $thumbWebPath;
    }

}