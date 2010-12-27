<?php
/**
 * ���� �������� ����� CTypedMap.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * ����� CTypedMap ������������ ����� � ���������� ������������� ����.
 *
 * ����� CTypedMap ��������� ����� {@link CMap} ����� ������������ �������, ��� �������
 * ����������� � ������ �������� ����� ���� � ��� �� ������������ ��� ������.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CTypedMap.php 2685 2010-11-29 11:03:47Z mdomba $
 * @package system.collections
 * @since 1.0
 */
class CTypedMap extends CMap
{
	private $_type;

	/**
	 * �����������.
	 * @param string $type ��� ������
	 */
	public function __construct($type)
	{
		$this->_type=$type;
	}

	/**
	 * ��������� ������� � �����.
	 * ����� �������������� ������������ ���������� ���������
	 * ������������ ������������� ���� ������������ ��������.
	 * @param integer $index ������������ �������
	 * @param mixed $item ����� �������
	 * @throws CException ����������, ���� ���������� ������ �������� ������� �����,
	 * ����� ������ ��� ������ ��� ������� �� ������������� ���������� ����
	 */
	public function add($index,$item)
	{
		if($item instanceof $this->_type)
			parent::add($index,$item);
		else
			throw new CException(Yii::t('yii','CTypedMap<{type}> can only hold objects of {type} class.',
				array('{type}'=>$this->_type)));
	}
}
