<?php
namespace Digitalis\Core\Models\Menu;

/**
 * MenuItem Représente un élément du menu
 *
 * @copyright  2018 IMEDIATIS SARL http://www.imediatis.net
 * @license    Intellectual property rights of IMEDIATIS SARL
 * @version    Release: 1.0
 * @author     Sylvin KAMDEM<sylvin@imediatis.net> (Back-end Developper)
 */
class MenuItem implements \Serializable
{
    private static $_iconMask = '<i class="fa fa-%s"></i>';
    private static $_labelMask = '%s';

    private static $_order = 1;
    /**
     * Identitiant de l'élément du menu
     * 
     * @var string
     */
    private $routeName;

    /**
     * Libellé du menu
     * 
     * @var string
     */
    private $label;

    /**
     * Détermine si c'est un groupe de menu
     * 
     * @var boolean
     */
    private $isGroup;

    /**
     * Ordre d'apparition du menu
     * 
     * @var int
     */
    private $order;

    /**
     * Icon fa du menu
     * 
     * @var string
     */
    private $icon;

    /**
     * Taleau des sous-menu en cas de groupe de menu
     * 
     * @var MenuItem[]
     */
    private $children;

    public function __construct($label, $routeName = null, $isGroup = false, $order = null, $icon = null)
    {
        ++self::$_order;
        $this->label = $label;
        $this->routeName = $routeName;
        $this->isGroup = $isGroup;
        $this->order = is_null($order) ? self::$_order : $order;
        $this->icon = $icon;
        $this->children = array();
    }

    /**
     * Ajoute un enfant à un menu
     *
     * @param MenuItem $child
     * @return MenuItem
     */
    public function addChildren(MenuItem $child)
    {
        $this->children[$child->getLabel()] = $child;
        return $this;
    }

    public function serialize()
    {
        return serialize(array(
            $this->routeName,
            $this->label,
            $this->isGroup,
            $this->order,
            $this->icon,
            $this->children
        ));
    }

    public function unserialize($serialized)
    {
        list(
            $this->routeName,
            $this->label,
            $this->isGroup,
            $this->order,
            $this->icon,
            $this->children
        ) = unserialize($serialized);
    }

    public function hasActiveChild($current)
    {
        $tcurrent = explode('.', $current);
        foreach ($this->children as $child) {
            $tchild = explode('.', $child->getRouteName());
            if ($tcurrent[0] == $tchild[0]) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retourne la valeur de $children
     *
     * @return MenuItem[]
     */
    public function getChildren()
    {
        return $this->children;
    }
    public function hasChildren()
    {
        return count($this->children) > 0;
    }

    /**
     * Définit la valeur de $children
     *
     * @param array $children
     */
    public function setChildren($children = array())
    {
        $this->children = $children;
    }

    /**
     * Retourne la valeur de $icon
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }
    public function sgetIcon()
    {
        return is_null($this->icon) ? null : sprintf(self::$_iconMask, $this->icon);
    }

    /**
     * Définit la valeur de $icon
     *
     * @param string $icon
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    /**
     * Retourne la valeur de $order
     *
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Définit la valeur de $order
     *
     * @param int $order
     */
    public function setOrder($order = 1)
    {
        $this->order = $order;
    }

    /**
     * Retourne la valeur de $isGroup
     *
     * @return boolean
     */
    public function getIsGroup()
    {
        return $this->isGroup;
    }

    /**
     * Définit la valeur de $isGroup
     *
     * @param boolean $isGroup
     */
    public function setIsGroup($isGroup = false)
    {
        $this->isGroup = $isGroup;
    }

    /**
     * Retourne la valeur de $label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    public function sgetLabel()
    {
        $label = sprintf(self::$_labelMask, $this->label);
        $label = '<span class="nav-label">' . $label . '</span>';
        return $this->isGroup ? $label . '<span class="fa arrow"></span>' : $label;
    }

    public function fullLabel()
    {
        return $this->sgetIcon() . $this->sgetLabel();
    }

    /**
     * Définit la valeur de $label
     *
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * Retourne la valeur de $routeName
     *
     * @return string
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * Définit la valeur de $routeName
     *
     * @param string $routeName
     */
    public function setRouteName($routeName)
    {
        $this->routeName = $routeName;
    }
}