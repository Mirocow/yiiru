<?php
/**
 * Файл класса CModelBehavior.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Класс CModelBehavior - это базовый класс для поведений, присоединяемых к компоненту модели.
 * Модель должна наследовать класс {@link CModel} или его классы-потомки.
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CModelBehavior.php 1678 2010-01-07 21:02:00Z qiang.xue $
 * @package system.base
 * @since 1.0.2
 */
class CModelBehavior extends CBehavior
{
	/**
	 * Объявляет события и их обработчики.
	 * Реализация по умолчанию возвращает события 'onBeforeValidate' и 'onAfterValidate' и имена методов их обработки.
	 * Если вы переопределяете данный метод, убедитесь в том, что результат родителя будет совмещен с возвращаемым значением.
	 * @return array события (ключи массива) и соответствующие методы-обработчики событий (значения массива).
	 * @see CBehavior::events
	 */
	public function events()
	{
		return array(
			'onBeforeValidate'=>'beforeValidate',
			'onAfterValidate'=>'afterValidate',
		);
	}

	/**
	 * Реагирует на событие {@link CModel::onBeforeValidate}.
	 * Переопределите данный метод, если вы хотите обрабатывать соответствующее событие в {@link owner контроллере-собственнике}.
	 * Вы можете установить свойство {@link CModelEvent::isValid} в значение false для прекращения выполнения процесса валидации.
	 * @param CModelEvent параметр события
	 */
	public function beforeValidate($event)
	{
	}

	/**
	 * Реагирует на событие {@link CModel::onAfterValidate} event.
	 * Переопределите данный метод, если вы хотите обрабатывать соответствующее событие в {@link owner контроллере-собственнике}.
	 * @param CEvent параметр события
	 */
	public function afterValidate($event)
	{
	}
}
