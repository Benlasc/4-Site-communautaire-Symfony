<?php

namespace App\Entity;

use App\Repository\AvatarRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity(repositoryClass=AvatarRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Avatar
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private $extension;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $alt;

    private $file;

    private $tempFilename;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function setExtension(string $extension): self
    {
        $this->extension = $extension;

        return $this;
    }

    public function getAlt(): ?string
    {
        return $this->alt;
    }

    public function setAlt(string $alt): self
    {
        $this->alt = $alt;

        return $this;
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(UploadedFile $file) 
    { 
        $this->file = $file; 
 
        // On vérifie si on avait déjà un fichier pour cette entité 
        if ($this->extension !== null) { 
            // On sauvegarde l'extension du fichier pour le supprimer plus tard 
            $this->tempFilename = $this->extension; 
 
            // On réinitialise les valeurs des attributs url et alt 
            $this->extension = null; 
            $this->alt = null; 
        } 
    }
    
    /** 
     * @ORM\PrePersist() 
     * @ORM\PreUpdate() 
     */
    public function preUpload() 
    { 
        // Si jamais il n'y a pas de fichier (champ facultatif) 
        if ($this->file === Null) { 
            return; 
        } 
 
        // Le nom du fichier est son id, on doit juste stocker également son extension 
        // Pour faire propre, on devrait renommer cet attribut en « extension », plutôt que « url
        $this->extension = $this->file->guessExtension(); 
 
        // Et on génère l'attribut alt de la balise <img>, à la valeur du nom du fichier sur le P
        $this->alt = $this->file->getClientOriginalName(); 
    }

    /** 
     * @ORM\PostPersist() 
     * @ORM\PostUpdate() 
     */
    public function upload() 
    { 
        // Si jamais il n'y a pas de fichier (champ facultatif) 
        if ($this->file === null) { 
            return; 
        } 
 
        // Si on avait un ancien fichier, on le supprime 
        if ($this->tempFilename !== Null) { 
            $oldFile = $this->getUploadRootDir().'/'.$this->id.'.'.$this->tempFilename; 
            if (file_exists($oldFile)) { 
                unlink($oldFile); 
            } 
        } 
 
        // On déplace le fichier envoyé dans le répertoire de notre choix 
        $this->file->move( 
            $this->getUploadRootDir(), // Le répertoire de destination 
            $this->id.'.'.$this->extension     // Le nom du fichier à créer, ici « id.extension » 
        ); 
    }

    /** 
     * @ORM\PreRemove() 
     */
    public function preRemoveUpload() 
    { 
        // On sauvegarde temporairement le nom du fichier, car il dépend de l'id 
        $this->tempFilename = $this->getUploadRootDir().'/'.$this->id.'.'.$this->extension; 
    }
    
    /** 
    * @ORM\PostRemove() 
    */
   public function removeUpload() 
   { 
       // En PostRemove, on n'a pas accès à l'id, on utilise notre nom sauvegardé 
       if (file_exists($this->tempFilename)) { 
           // On supprime le fichier 
           unlink($this->tempFilename); 
       } 
   } 
   
   public function getUploadDir() 
   { 
       // On retourne le chemin relatif vers l'image pour un navigateur 
       return 'uploads/avatars'; 
   } 

   protected function getUploadRootDir() 
   { 
       // On retourne le chemin relatif vers l'image pour notre code PHP 
       return __DIR__.'/../../public/'.$this->getUploadDir(); 
   }  
   
   public function getWebPath() 
   { 
       return $this->getUploadDir() . '/' . $this->getId() . '.' . $this->getExtension(); 
   } 

}