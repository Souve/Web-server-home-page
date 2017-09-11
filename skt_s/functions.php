<?php
    /**
     * Skatek Server Class
     * @category Local Home Web Server
     * @since 0.2.0
     * @author Souvenance Kavunga <skavunga@gmail.com>
     * @license http://opensource.org/licenses/gpl-license.php GNU Public License
     * @link http://www.skatek.esy.es
     * @uses Skatek Destinee pour le server de la Skatek Corporation
     */
    require("skatek.inc.php");
    class SkatekServer extends Skatek
    {
        private $absolutePath;
        private $currentDir;
        private $zero = false;
        private $exceptions = [];

        public function __construct($dir = null, $dblink = 'http://skatek.db') {
            if ($dir == null) { $dir = __DIR__; }
            $this->setAbsolutePath($dir . DIRECTORY_SEPARATOR);
            $this->setCurrentDir($this->getDirectoryFromQuery());

            $this->setDbLink($dblink);
            
            if ($this->getQuery('href')) { $this->setZero(true); }
        }
        
        /**
         * Definit le chemin par defaut
         * @param type $param
         */
        public function setAbsolutePath($param) {
            $this->absolutePath = $param;
        }
        
        /**
         * Definit le repertoire courant
         * @param type $param
         */
        public function setCurrentDir($param) {
            $this->currentDir = $param;
        }
        
        /**
         * Retourne le repertoire courant
         * @return type
         */
        public function getCurrentDir() {
            return $this->currentDir;
        }
        
        /**
         * Retourne le chemin par defaut des directory
         * @return type
         */
        public function getAbsolutePath() {
            return $this->absolutePath;
        }        

        public function getZero()
        {
            return $this->zero;
        }

        public function setZero($value)
        {
            $this->zero = $value;
        }

        public function setExceptions($value = [])
        {
            if (!is_array($value)) { $value = [$value]; }
            $this->exceptions = $value;
        }

        /**
         * On scanne le $currentDir ou le $dir puis on envoi les dossiers
         * et les fichiers dans un array
         * @param string $dir Dir a scanner
         * @return boolean|array 
         */
        public function obtenirContenus($dir = null) {
            if ($dir == null) { $repertoire = $this->getAbsolutePath() . $this->getCurrentDir(); }
            else { $repertoire = $this->getAbsolutePath() . $dir; }
            
            if (! is_dir($repertoire)) { return false; }
            $contenus = [
                'dirs' => [],
                'files' =>[]
            ];
            
            $dir = opendir($repertoire);
            
            while (($contenu = readdir($dir)))
            {
                $fichier = $repertoire . DIRECTORY_SEPARATOR . $contenu;
                if (is_dir($fichier)) { $contenus['dirs'][] = $contenu; } 
                else { $contenus['files'][] = $contenu; }
            }
            return $contenus;
        }
        
        /**
         * On recupere le nom du respertoire a partir de la query
         * passee dans l'URL de la method GET, puis on en fait le chemin si existe
         * sinon, on renvoi /
         * @return string
         */
        public function getDirectoryFromQuery() {
            $absolute = '/';
            if ($this->getQuery('dir')) 
            { 
                $dir = $this->getAbsolutePath() . $this->getQuery('dir');
                $dir = $this->removeChar($dir, '.');

                if ($dir == $this->getAbsolutePath()) { return $absolute; }

                // echo $dir; exit;
                if (is_dir($dir)) { return $this->getQuery('dir'); }                
            }
            return $absolute;
        }
        
        /**
         * Recherche dans un repertoire s'il y a un fichier index
         * @param type $dir Repertoire de recherche
         * @return boolean
         */
        public function rechercheIndex($dir) {
            if (!is_dir($dir)) { return false; }
            $index = ["index.html", "index.php"];
            $fichiers = $this->obtenirContenus($dir);
            
            foreach ($fichiers['files'] as $fichier){
                if (in_array(strtolower($fichier), $index)) { return $fichier; }
            }
            return false;
        }
        
        /**
         * Permet d'obtenir la liste des repertoires ainsi que leurs liens
         * d'access et s'il on une page index 
         * @return boolean|array
         */
        public function obtenirDirectories() {
            if ($this->getZero()) { return []; }
            $dirs = $this->obtenirContenus()['dirs'];
            $repertoires = []; 
            
            foreach ($dirs as $k => $dir){
                if (in_array($dir, $this->getExceptions())){ continue;}
                $repertoires[$k]['name'] = ucfirst($dir);
                
                if($this->rechercheIndex($dir)){
                    $repertoires[$k]['link'] = $this->getCurrentDir() != '/' ? $this->getCurrentDir() . '/' . $dir : $this->getCurrentDir() .$dir;                
                    $repertoires[$k]['index'] = true;
                } else {
                    $repertoires[$k]['link'] = $this->getCurrentDir() != '/' ? $this->getCurrentDir() . '/' . $dir : $dir;                
                    $repertoires[$k]['index'] = false;
                }
            }
            return $repertoires;
        }
        
        /**
         * Obtenir la liste des fichiers dans le dossier courant
         * @return array
         */
        public function obtenirFiles($zero = false) {
            if ($this->getZero()) { return []; }
            $files = $this->obtenirContenus()['files'];
            $fichiers = [];
            
            foreach ($files as $k => $file):
                if (in_array($file, $this->getExceptions())){ continue;}
                $fichiers[$k]['name'] = ucfirst($file);
                $fichiers[$k]['link'] = $this->getCurrentDir() != '/' ? $this->getCurrentDir() . '/' . $file : $this->getCurrentDir() .$file;
                
            endforeach;
            return $fichiers;
        }
        
        /**
         * Retourne la liste des exceptions
         * Des fichiers a ignorer
         * @return array
         */
        public function getExceptions() {
            return array_merge($this->exceptions, ['.', '..', 'skt_s', 'index.php', 'index.html', 'favicon.ico']);
        }
    }