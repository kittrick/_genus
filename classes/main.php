<?php

//==============================================
//! Genus, PHP file browser by Kit MacAllister
//==============================================

class Genus {
	
	//=========================================================================
	//! These are default vars and are empty by default. They get filled out
	//  by constructor functions and are echoed by the index.php file.
	//=========================================================================
	
	/* Page Stuff */
	public $title;
	public $body;
	
	/* Default Rendering Vars */
	public $location = '../'; 														// Relative URL Path
	public $sort = 'name';															// Sort category
	public $sortArray = array('name','created','modified','size','mime','type');	// Which sort columns to display
	public $ascending = true;														// Ascending or descending, bool
	public $hiddenFiles = false;													// Show or hide hidden files, bool
	public $foldersFirst = false;													// Put folders first or sort normally, bool
	
	
	/* Run our Page Construction */
	function __construct(){
		
		/* Let's Generate our Page */
		$page = new Page( $this->location, $this->sort, $this->sortArray, $this->ascending, $this->hiddenFiles, $this->foldersFirst );
		$this->title = $page->title;
		$this->body = $page->body;
	}
}

//=======================================================================
//! The Page class parses file data and wraps it in pretty pretty HTML.
//=======================================================================

class Page {

	/* Public Vars */
	public $location; 		// The relative url path
	public $sort;			// The sort category
	public $sortArray;		// Which sort columns to display
	public $ascending;		// Ascending or descending, bool
	public $hiddenFiles;	// Show or hide hidden files, bool
	public $foldersFirst;	// Put folders first or sort normally, bool
	public $title;			// HTML Page Title
	public $body;			// HTML formatted Body
	public $folder;			// Folder object for reference
	
	/* Set Vars */
	function __construct( $location, $sort, $sortArray, $ascending, $hiddenFiles, $foldersFirst ){
		$this->location = $location;
		$this->sort = $sort;
		$this->sortArray = $sortArray;
		$this->ascending = $ascending;
		$this->hiddenFiles = $hiddenFiles;
		$this->foldersFirst = $foldersFirst;
		$this->folder = new Folder($location, $sort, $ascending, $hiddenFiles, $foldersFirst);
		$this->title = $this->title();
		$this->body = $this->body();
	}

	/* This function creates a page title based on the Parent folder Name */
	public function title(){
		$title = $this->folder->title;
		return $title;
	}
	
	/* This function Formats the body content */
	public function body(){
		
		/* Start HTML Formatted output */
		$output = '<table><thead><tr>';
		
		/* Table Head */
		foreach($this->sortArray as $category){
			
			$output.= '<th';
			if($category == $this->sort){
				$output .= ' class="active';
				if($this->folder->ascending == true){
					$output .= ' ascending">';
				}else{
					$output .= ' descending">';
				}
			} else {
				$output .= '>';
			}
			$output .= '<h3><a href="#!/">';
			if($category == 'created' || $category == 'modified'){
				/* Eg. Date Modified */
				$output .= 'Date ';
			}
			$output .= ucwords($category).'</a></h3></th>';
		}
		
		/* End Table head, start table body */
		$output .= '</tr></thead>'."\n".'<tbody>';
		
		/* Table Body */
		$i = 0;
		foreach($this->folder->fileArray as $file){
			$output .= '<tr class="';
			if($i % 2 == 0){ //Check if even
				$output .= 'even';
			} else {
				$output .= 'odd';
			}
			
			/* Check for Directory */
			if($file->type = 'dir') $output .= ' folder';
			$output .= '">';
			foreach($this->sortArray as $category){
				$output .= '<td>'.$file->$category.'</td>';
			}
			$output .= '</tr>';
		}
		
		/* End Table */
		$output .= '</tbody></table>';
		/* Return Output */
		return $output;
	}
}

//==========================================================================
//! The Folder class organizes all of our file data into a nice directory.
//==========================================================================

class Folder {
	
	/* The Folder's Title */
	public $title;
	public $location;
	public $sort;
	public $ascending;
	public $hiddenFiles;
	public $foldersFirst;
	public $files;
	public $fileArray;
	
	/* This gets all of the folder info based on it's location */
	function __construct( $location, $sort , $ascending , $hiddenFiles , $foldersFirst ){
		
		/* Sets the folder's title */
		$this->title = 'wassup';
		
		/* Sets other folder info */
		$this->sort = $sort;
		$this->ascending = $ascending;
		$this->files = scandir($location);
		foreach($this->files as $file){
			$tempfile = new File($file, $location);
			if($hiddenFiles == false && $tempfile->hidden != true){
				$this->fileArray[] = $tempfile;
			}elseif($hiddenFiles == true){
				$this->fileArray[] = $tempfile;
			}
		}
		
		/* File Sorting */
		foreach($this->fileArray as $key => $val){
			$sortArray[] = $val->$sort;
		}
		sort($sortArray);
		foreach($sortArray as $key){
			foreach($this->fileArray as $file => $val){
				if($key == $val->$sort){
					$newArray[] = $val;
					unset($this->fileArray[$file]);
				}
			}
		}
		$directoryArray = $newArray;
		if($ascending == false){
			$directoryArray = array_reverse($directoryArray);
		}
		$this->fileArray = $directoryArray;
	}
}

//============================================================
//! This class reads file content and creates a PHP object
//  that can be turned used by the directory object.
//============================================================

class File extends Format {
	
	/* Vars */
	public $absolutePath;
	public $name;
	public $href;
	public $directoryArray;
	public $created;
	public $modified;
	public $type;
	public $kind;
	public $size;
	public $hidden;
	public $mime;
	
	/* Creates File Info Object */
	function __construct($file, $parent, $calculateFolders = false){
		/* Hidden Files */
		$exceptionArray = array(
			"Icon\r" // OSX's Icon Alias Files
		);
		$this->absolutePath = './'.$parent.'/'.$file;
		$this->name = $file;
		$this->href = $file;
		$this->directoryArray = explode('/',$this->absolutePath);
		$this->created = $this->formatDate(filectime($this->absolutePath));
		$this->modified = $this->formatDate(filemtime($this->absolutePath));
		$this->type = filetype($this->absolutePath);
		
		if($this->type == 'dir'){
			$this->kind = 'Folder';
		}else{
			$end = explode('.',$this->name);
			$this->extension = end($end);
			$this->kind = strtoupper($this->extension).' File';
		}
		
		if($this->type == 'dir' && $calculateFolders == false){
			$this->size = '--';
		}else{
			$this->size = $this->formatFileSize(filesize($this->absolutePath));
		}
		$this->mime = mime_content_type($this->absolutePath);
		
		if(substr($this->name,0,1) == '.'){
			$this->hidden = true;
		}elseif(in_array($this->name,$exceptionArray)){
			$this->hidden = true;
		}else{
			$this->hidden = false;
		}
		return $this;
	}
}

//================================================
//! The Format class helps display raw file data
//================================================

class Format {

	/* Format File Dates */
	function formatDate($timestamp){
		$format = 'l, F j, g:i A';
		$date = date($format,$timestamp);
		return $date;
	}
	
	/* Format File Size */
	function formatFileSize($size){
		
		$output = '';
		
		if($size < 1000){
			$size = round(($size), 2);
			$output .= $size.' B';
		}elseif($size < 1000000){
			$size = round(($size/1000), 2);
			$output .= $size.' KB';
		}elseif($size < 1000000000){
			$size = round(($size/1000000), 2);
			$output .= $size.' MB';
		}elseif($size < 1000000000000){
			$size = round(($size/1000000000), 2);
			$output .= $size.' GB';
		}
		
		return $output;
	}
}