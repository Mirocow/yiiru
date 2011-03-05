<?php
/**
 * ���� ������ CJuiDraggable.
 *
 * @author Sebastian Thierer <sebathi@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

Yii::import('zii.widgets.jui.CJuiWidget');

/**
 * ������ CJuiDraggable ���������� ��������������� �������.
 *
 * ������ CJuiDraggable ������������� {@link http://jqueryui.com/demos/draggable/ ������ JUI draggable}.
 *
 * ��� ������������� ������� ������� ����� �������� � ������������� ��������� ���:
 * <pre>
 * $this->beginWidget('zii.widgets.jui.CJuiDraggable', array(
 *     // �������������� javascript-����� ��� ������� ���������������� ��������
 *     'options'=>array(
 *         'scope'=>'myScope',
 *     ),
 * ));
 *     echo '���������� ���������������� ��������';
 *     
 * $this->endWidget();
 * 
 * </pre>
 *
 * ���������� �������� {@link options} ����� ���������� �����, ������������ � ������ ���������������� ��������.
 * ���������� � {@link http://jqueryui.com/demos/draggable/ ������������ � ������� JUI draggable}
 * �� ������� ��������� ����� (��� ���-��������).
 *
 * @author Sebastian Thierer <sebathi@gmail.com>
 * @version $Id: CJuiDraggable.php 2799 2011-01-01 19:31:13Z qiang.xue $
 * @package zii.widgets.jui
 * @since 1.1
 */
class CJuiDraggable extends CJuiWidget
{
	/**
	 * @var string ��� ���� ���������� ���������������� ��������. �� ��������� - 'div'
	 */
	public $tagName='div';

	/**
	 * ���������� ����������� ��� ���������������� ��������.
	 * ����� ����� ������������ ��������� javascript-���
	 */
	public function init(){
		parent::init();
		
		$id=$this->getId();
		if (isset($this->htmlOptions['id']))
			$id = $this->htmlOptions['id'];
		else
			$this->htmlOptions['id']=$id;
		
		$options=empty($this->options) ? '' : CJavaScript::encode($this->options);
		Yii::app()->getClientScript()->registerScript(__CLASS__.'#'.$id,"jQuery('#{$id}').draggable($options);");

		echo CHtml::openTag($this->tagName,$this->htmlOptions)."\n";
	}

	/**
	 * ���������� ����������� ��� ���������������� ��������
	 */
	public function run(){
		echo CHtml::closeTag($this->tagName);
	}
	
}


