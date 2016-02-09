<?php

namespace backend\widgets\ImageAreaSelect;

use yii\web\AssetBundle;


class CropImageAsset extends AssetBundle {

    public $sourcePath = '@cropimage';
    public $basePath = '@cropimage';

    public $css = [
        'css\imgareaselect-default.css',
    ];
    public $js = [
        'js\jquery.imgareaselect.pack.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];

    public function init() {
		Yii::setAlias('@cropimage', __DIR__);
		return parent::init();
	}

}
