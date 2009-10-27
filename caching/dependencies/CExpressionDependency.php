<?php
/**
 * Файл класса CExpressionDependency.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Компонент CExpressionDependency представляет зависимость, основанную на результате PHP-выражения.
 *
 * Компонент CExpressionDependency выполняет проверку, основанную на результате {@link expression PHP-выражения}.
 * Зависимость является неизменной только в том случае, если результат такой же как
 * результат, вычисленный при сохранении данных в кэш.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CExpressionDependency.php 1483 2009-10-27 15:57:33Z qiang.xue $
 * @package system.caching.dependencies
 * @since 1.0
 */
class CExpressionDependency extends CCacheDependency
{
	/**
	 * @var string PHP-выражение, результат которого используется для определения зависимости.
	 * Начиная с версии 1.0.11, выражение также может быть допустимым обратным вызовом PHP,
	 * включая имя функции, имя метода класса (array(ClassName/Object, MethodName))
	 * или анонимная функция (PHP 5.3.0+). В функцию/метод будет передан один параметр -
	 * сам объект зависимости.
	 */
	public $expression;

	/**
	 * Конструктор.
	 * @param string PHP-выражение, результат которого используется для определения зависимости.
	 */
	public function __construct($expression='true')
	{
		$this->expression=$expression;
	}

	/**
	 * Генерирует данные, необходимые для определения изменения зависимости.
	 * Метод возвращает результат PHP-выражения.
	 * @return mixed данные, необходимые для определения изменения зависимости
	 */
	protected function generateDependentData()
	{
		if(is_callable($this->expression))
			return call_user_func($this->expression, $this);
		else
			return @eval('return '.$this->expression.';');
	}
}
