# yii2-widget-crop-image
=================
This widget allows you to select a rectangular area on the source image on the server, cache the new image and use it.

##Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist evgkan/yii2-widget-crop-image "*"
```

or add

```
"evgkan/yii2-widget-crop-image": "*"
```

to the require section of your `composer.json` file.


##Use

###In admin(profile) view:
```
<?= $form->field($model, 'photo')->widget(CropImage::className()) ?>
```
After you submit the form the model attribute will contain an array with the data framing, like this:
```
[
  'imgSrc' => <path>,
  'x1' => <left 0..1>,
  'y1' => <top 0..1>,
  'x2' => <right 0..1>,
  'y2' => <bottom 0..1>
]
```

###In model:
```
public function getCropPhoto(){
  return CropImage::getCropImage($this->photo);
}
```
This method searches the cached image, using data framing. If cached image is absent, a new one is created.
###In common view:
```
<img src="<?= model->photo ?>">       <!-- original photo -->
<img src="<?= model->cropPhoto ?>"> <!-- croped and cached photo -->
```
##Warning!
The widget uses a serverside file manager mihaildev/yii2-elfinder. It needs to be properly configured to access the server file system.
