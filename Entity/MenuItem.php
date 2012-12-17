<?php

namespace Networking\InitCmsBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Networking\InitCmsBundle\Entity\Page;

/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Entity
 * @ORM\Table(name="cms_menu")
 * @ORM\Entity(repositoryClass="Networking\InitCmsBundle\Entity\MenuItemRepository")
 * @ORM\HasLifecycleCallbacks
 */
class MenuItem implements \IteratorAggregate
{
    protected $options = array();

    const PATH_SEPARATOR = '/';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="Networking\InitCmsBundle\Entity\Page", inversedBy="menuItem", cascade={"persist"})
     * @ORM\JoinColumn(name="page_id", referencedColumnName="id")
     */
    protected $page;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     */
    protected $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    protected $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    protected $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(name="root", type="integer", nullable=true)
     */
    protected $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="MenuItem", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="MenuItem", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    protected $children;

    /**
     * @var string $locale;
     *
     * @ORM\Column(name="locale")
     */
    protected $locale;

    public function __construct()
    {
        $this->children = new ArrayCollection();

    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->name;
    }

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        if ($this->getParent()) {
            $this->setLocale();
        }
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param  Page     $page
     * @return MenuItem
     */
    public function setPage(Page $page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Get conversation
     *
     * @return Page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param $name
     * @return MenuItem
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return Name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param MenuItem $parent
     */
    public function setParent(MenuItem $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return mixed
     */
    public function _toString()
    {
        return $this->name;
    }

    /**
     * @param $lft
     * @return MenuItem
     */
    public function setLft($lft)
    {
        $this->lft = $lft;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLft()
    {
        return $this->lft;
    }

    /**
     * @param $lvl
     */
    public function setLvl($lvl)
    {
        $this->lvl = $lvl;
    }

    /**
     * @return mixed
     */
    public function getLvl()
    {
        return $this->lvl;
    }

    /**
     * @param $rgt
     * @return MenuItem
     */
    public function setRgt($rgt)
    {
        $this->rgt = $rgt;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRgt()
    {
        return $this->rgt;
    }

    public function setRoot($root)
    {
        $this->root = $root;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRoot()
    {
        return $this->root;
    }

    public function getMenu()
    {
        return $this->getRootParent($this);
    }

    /**
     * @param  MenuItem $menuItem
     * @return MenuItem
     */
    public function getRootParent(MenuItem $menuItem)
    {
        if (!$menuItem->getId()) return false;

        if ($parent = $menuItem->getParent()) {
            return $this->getRootParent($parent);
        }

        return $menuItem;
    }

    /**
     * @param  int           $level
     * @return bool|MenuItem
     */
    public function getParentByLevel($level = 1)
    {
        if ($level === $this->getLvl()) return $this;

        if (!$this->getParent()) return false;

        if ($level === $this->getParent()->getLvl()) {
            return $this->getParent();
        }

        return $this->getParent()->getParentByLevel($level);
    }

    /**
     * @param  MenuItem $menuItem
     * @return MenuItem
     */
    public function setMenu(MenuItem $menuItem)
    {
        $this->setParent($menuItem);

        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    public function getActiveChildren()
    {
        $children = new ArrayCollection();
        foreach ($this->getChildren() as $child) {
            if (!$child->getPage()->isActive()) continue;
            $children->add($child);
        }

        return $children;
    }

    /**
     * @param $path
     */
    public function setPath($path)
    {
        return;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        if (!$this->getPage()) return;
        return $this->getPage()->getContentRoute()->getPath();
    }

    /**
     * @return string
     */
    public function getRouteId()
    {
        if (!$this->getPage()) return;
        return $this->getPage()->getContentRoute()->getId();
    }

    /**
     * @param  null     $locale
     * @return MenuItem
     */
    public function setLocale($locale = null)
    {
        if (is_null($locale)) {
            $locale = $this->getParent()->getLocale();
        }
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Implements IteratorAggregate
     */
    public function getIterator()
    {
        return $this->children->getIterator();
    }

}