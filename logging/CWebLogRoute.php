<?php
/**
 * Файл класса CWebLogRoute.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Объект класса CWebLogRoute показывает содержимое журнала на веб-странице.
 *
 * Содержимое журнала может отображаться либо в конце текущей страницы либо
 * в окне консоли FireBug (если свойство {@link showInFireBug} установлено в true).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CWebLogRoute.php 641 2009-02-08 20:24:39Z qiang.xue $
 * @package system.logging
 * @since 1.0
 */
class CWebLogRoute extends CLogRoute
{
	/**
	 * @var boolean должно ли содержимое журнала отображаться в окне консоли
	 * FireBug вместо окна браузера. По умолчанию установлено в false.
	 */
	public $showInFireBug=false;

	/**
	 * Отображает сообщения журнала.
	 * @param array list of log messages
	 */
	public function processLogs($logs)
	{
		$this->render('log',$logs);
	}

	/**
	 * Рендерит представление.
	 * @param string имя представления (имя файла без расширения). Предполагается,
	 * что файл находится в каталоге framework/data/views.
	 * @param array данные, передающиеся в представление
	 */
	protected function render($view,$data)
	{
		if($this->showInFireBug)
			$view.='-firebug';
		else
		{
			$app=Yii::app();
			if(!($app instanceof CWebApplication) || $app->getRequest()->getIsAjaxRequest())
				return;
		}
		$viewFile=YII_PATH.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.$view.'.php';
		include(Yii::app()->findLocalizedFile($viewFile,'en'));
	}
}

