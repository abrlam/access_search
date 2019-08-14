<?php

/* 	Remove keywords with 'access' and save keyword value to GLOBALS['searchAccess']
	Input $keywords is the search organized as an array of 'IDENTIFIER:VALUE'
	Return a copy of $keywords with no access identifiers

	access must be removed in this hook. 'access' is not a valid metadata and
	therefore will cause search_do to return an empty result set.
*/
function HookAccess_searchAllDosearchmodifykeywords($keywords){
	$searchAccess = array();
	$keywordsNoAccess = array();
	# push each entry into searchAccess for 'access', otherwise push into keywordsNoAccess
	foreach ($keywords as $index=>$keyword){
		$keywordParts = explode(":", $keyword);
		if($keywordParts[0] == "access"){
			# keyword should be sanitized against sql injection. This is to guarantee
			# injection is impossible. Alternatively, this filters out many bad inputs
			if(is_numeric($keywordParts[1]))
				array_push($searchAccess, intval($keywordParts[1]));
		}else{
			array_push($keywordsNoAccess, $keyword);
		}
	}
	# set in globals for temporary storage between functions
	$GLOBALS['searchAccess'] = $searchAccess;

	/**
	If if($keywordsNoAccess) fails, access keywords are not removed. Thus, the search is returned due to a bad keyword
	A solution is to substitute [] with [""]
	*/
	if(!$keywordsNoAccess){
		return array("");
	}
	return $keywordsNoAccess;
}

/*	Add sql filter for desired accesses.

	GLOBALS['searchAccess'] should contain nothing or a list of integers
*/
function HookAccess_searchAllUserownfilter(){
	$accesslist_str = implode(',', $GLOBALS['searchAccess']);
	if($accesslist_str){
		return " JOIN (SELECT ref FROM resource WHERE access IN (".$accesslist_str.")) AS a ON a.ref = r.ref ";
	}
}

/*	DEPRECATED. Alternative to SQL filtering. Before using this hook, be aware that resource
	space only presents page numbers if there are a certain number results in the array. Since this filter will shorten the array, Resource Space will claim there are no more pages even when there are

	Filter results by GLOBALS['searchAccess'] or do nothing if searchAccess is empty
	Input $results = list of filtered entries
	Input $search is in hook prototype, it is not used
	Return a list of results filtered by global 'searchAccess'
*/
/*
function HookAccess_searchAllProcess_search_results($result, $search){
	$accessFilter = $GLOBALS['searchAccess'];
	$resultAccessFilter = array();

	# if searchAccess was not set, don't filter
	if(empty($accessFilter)){
		return $result;
	}

	# push result entries that fit accessFilter
	foreach($result as $entry){
		# match accessFilter by value
		if(in_array($entry['access'], $accessFilter)){
			array_push($resultAccessFilter, $entry);
		}
	}

	return $resultAccessFilter;
}
*/