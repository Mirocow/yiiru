<?php
/**
 * Файл класса CWebLogRoute.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Объект класса CWebLogRoute показывает содержимое журнала на веб-странице.
 *
 * Содержимое журнала может отображаться либо в конце текущей страницы либо
 * в окне консоли FireBug (если свойство {@link showInFireBug} установлено в true).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CWebLogRoute.php 2799 2011-01-01 19:31:13Z qiang.xue $
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
	 * @var boolean должно ли игнорироваться журналирование в FireBug'е для ajax-вызовов. По умолчанию - true.
	 * Данная настройка должна использоваться осторожно, т.к. ajax-вызов возвращает в качестве результирующих данных все выходные данные.
	 * Например, если ajax-вызов ожидает результат типа json, то любые выходные данные журнала будут вызывать ошибку выполнения ajax-вызова.
	 */
	public $ignoreAjaxInFireBug=true;

	/**
	 * Отображает сообщения журнала.
	 * @param array $logs список сообщений журнала
	 */
	public function processLogs($logs)
	{
		$this->render('log',$logs);
	}

	/**
	 * Рендерит представление.
	 * @param string $view имя представления (имя файла без расширения). Предполагается,
	 * что файл находится в каталоге framework/data/views.
	 * @param array $data данные, передающиеся в представление
	 */
	protected function render($view,$data)
	{
		$app=Yii::app();
		$isAjax=$app->getRequest()->getIsAjaxRequest();

		if($this->showInFireBug)
		{
			if($isAjax && $this->ignoreAjaxInFireBug)
				return;
			$view.='-firebug';
		}
		else if(!($app instanceof CWebApplication) || $isAjax)
			return;

		$viewFile=YII_PATH.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.$view.'.php';
		include($app->findLocalizedFile($viewFile,'en'));
	}
}

