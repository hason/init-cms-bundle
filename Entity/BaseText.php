<?php

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Entity;

use Doctrine\ORM\Mapping as ORM,
    Networking\InitCmsBundle\Entity\ContentInterface,
    Ibrows\Bundle\SonataAdminAnnotationBundle\Annotation as Sonata;


/**
 * Networking\InitCmsBundle\Model
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks()
 *
 * @author net working AG <info@networking.ch>
 */
abstract class BaseText implements ContentInterface
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;


    /**
     * @var string $content
     * @ORM\Column(name="text", type="text", nullable=true)
     * @Sonata\FormMapper(name="text", type="ckeditor", options={"label_render" = false, "required"=false, "property_path" = false, "attr"={"class"="wysiwyg-editor"}}, fieldDescriptionOptions={"inline_block" = true})
     */
    protected $text;

    /**
     * @var \DateTime $createdAt
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime $updatedAt
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected $updatedAt;

    /**
     *
     */
    public function __clone(){
        $this->id = null;
    }

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {

        $this->createdAt = $this->updatedAt = new \DateTime("now");
    }

    /**
     * Hook on pre-update operations
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->updatedAt = new \DateTime('now');
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set content
     *
     * @param  text $text
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get content
     *
     * @return text
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set createdAt
     *
     * @return $this
     */
    public function setCreatedAt()
    {
        $this->createdAt = new \DateTime();

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param  \DateTime   $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }


    /**
     * @param array $params
     * @return array
     */
    public function getTemplateOptions($params = array())
    {
        return array('text' => $this->getText());
    }

    /**
     * @return array
     */
    public function getAdminContent()
    {
        return array(
            'content' => array('text' => $this),
            'template'  => 'NetworkingInitCmsBundle:Text:admin_text_block.html.twig'
        );
    }

    /**
     * @return string
     */
    public function getContentTypeName()
    {
        return 'Text Block';
    }

}
