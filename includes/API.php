<?php

namespace RRZE\Glossary;

defined('ABSPATH') || exit;

define ('ENDPOINT', 'wp-json/wp/v2/glossary' );

class API {

    private $aAllCats = array();

    public function setDomain( $shortname, $url, $domains ){
        // returns array('status' => TRUE, 'ret' => array(cleanShortname, cleanUrl)
        // on error returns array('status' => FALSE, 'ret' => error-message)
        $aRet = array( 'status' => FALSE, 'ret' => '' );
        $cleanUrl = trailingslashit( preg_replace( "/^((http|https):\/\/)?/i", "https://", $url ) );
        $cleanShortname = strtolower( preg_replace('/[^A-Za-z0-9]/', '', $shortname ) );

        if ( in_array( $cleanUrl, $domains )){
            $aRet['ret'] = $url . __( ' is already in use.', 'rrze-glossary' );
            return $aRet;
        }elseif ( array_key_exists( $cleanShortname, $domains )){
            $aRet['ret'] = $cleanShortname . __( ' is already in use.', 'rrze-glossary' );
            return $aRet;
        }else{
            $request = wp_remote_get( $cleanUrl . ENDPOINT . '?per_page=1' );
            $status_code = wp_remote_retrieve_response_code( $request );

            if ( $status_code != '200' ){
                $aRet['ret'] = $cleanUrl . __( ' is not valid.', 'rrze-glossary' );
                return $aRet;
            }else{
                $content = json_decode( wp_remote_retrieve_body( $request ), TRUE );

                if ($content){
                    $cleanUrl = substr( $content[0]['link'], 0 , strpos( $content[0]['link'], '/glossary' ) ) . '/';
                }else{
                    $aRet['ret'] = $cleanUrl . __( ' is not valid.', 'rrze-glossary' );
                    return $aRet;    
                }
            } 
        }

        $aRet['status'] = TRUE;
        $aRet['ret'] = array( 'cleanShortname' => $cleanShortname, 'cleanUrl' => $cleanUrl );
        return $aRet;
    }

    protected function isRegisteredDomain( &$url ){
        return in_array( $url, $this->getDomains() );
    }

    public function getDomains(){
        $domains = array();
        $options = get_option( 'rrze-glossary' );
        if ( isset( $options['registeredDomains'] ) ){
            foreach( $options['registeredDomains'] as $shortname => $url ){
                $domains[$shortname] = $url;
            }	
        }
        asort( $domains );
        return $domains;
    }
    

    protected function getTaxonomies( $url, $field, &$filter ){
        $aRet = array();    
        $url .= ENDPOINT . '_' . $field;    
        $slug = ( $filter ? '&slug=' . $filter : '' );
        $page = 1;

        do {
            $request = wp_remote_get( $url . '?page=' . $page . $slug );
            $status_code = wp_remote_retrieve_response_code( $request );
            if ( $status_code == 200 ){
                $entries = json_decode( wp_remote_retrieve_body( $request ), true );
                if ( !empty( $entries ) ){
                    foreach( $entries as $entry ){
                        if ( $entry['source'] == 'website' ){                            
                            if ( $entry['children'] ) {
                                foreach( $entry['children'] as $childname ){
                                    $aRet[$entry['name']][$childname] = array();        
                                }
                            }else{
                                $aRet[$entry['name']] = array();
                            }
                        }
                    }
                    foreach( $aRet as $name => $aChildren ){
                        foreach ( $aChildren as $childname => $val ){
                            if ( isset( $aRet[$childname] ) ){
                                $aRet[$name][$childname] = $aRet[$childname];
                            }
                        }
                    }
                }
            }
            $page++;   
        } while ( ( $status_code == 200 ) && ( !empty( $entries ) ) );
        return $aRet;
    }

    
    public function sortIt( &$arr ){
        uasort( $arr, function($a, $b) {
            return strtolower( $a ) <=> strtolower( $b );
        } );
    }
    
    public function deleteTaxonomies( $source, $field ){
        $args = array(
            'hide_empty' => FALSE,
            'meta_query' => array(
                array(
                   'key'       => 'source',
                   'value'     => $source,
                   'compare'   => '='
                )
            ),
            'taxonomy'  => 'glossary_' . $field,
            'fields' => 'ids'
            );
        $terms = get_terms( $args );
        foreach( $terms as $ID  ){
            wp_delete_term( $ID, 'glossary_' . $field );
        }
    }


    public function deleteCategories( $source ){
        $this->deleteTaxonomies( $source, 'category');
    }

    public function deleteTags( $source ){
        $this->deleteTaxonomies( $source, 'tag');
    }

    protected function setCategories( &$aCategories, &$shortname ){
        $aTmp = $aCategories;
        foreach ( $aTmp as $name => $aDetails ){
            $term = term_exists( $name, 'glossary_category' );
            if ( !$term ) {
                $term = wp_insert_term( $name, 'glossary_category' );
            }
            update_term_meta( $term['term_id'], 'source', $shortname );    
            foreach ( $aDetails as $childname => $tmp ) {
                $childterm = term_exists( $childname, 'glossary_category' );
                if ( !$childterm ) {
                    $childterm = wp_insert_term( $childname, 'glossary_category', array( 'parent' => $term['term_id'] ) );
                    update_term_meta( $childterm['term_id'], 'source', $shortname );    
                }
            }
            if ( $aDetails ){
                $aTmp = $aDetails;
            }
        }
    }

    
    public function sortAllCats( &$cats, &$into ) {
        foreach ($cats as $ID => $aDetails) {
            $into[$ID]['slug'] = $aDetails['slug'];
            $into[$ID]['name'] = $aDetails['name'];            
            if ( $aDetails['parentID'] ) {
                $parentID = $aDetails['parentID'];
                $into[$parentID][$ID]['slug'] = $aDetails['slug'];
                $into[$parentID][$ID]['name'] = $aDetails['name'];
            }
            unset( $cats[$parentID] );
        }    
        $this->sortAllCats( $cats, $into );
    }


    public function sortCats(Array &$cats, Array &$into, $parentID = 0, $prefix = '' ) {
        $prefix .= ( $parentID ? '-' : '' );
        foreach ($cats as $i => $cat) {
            if ( $cat->parent == $parentID ) {
                $into[$cat->term_id] = $cat;                
                unset( $cats[$i] );
            }
            $this->aAllCats[$cat->term_id]['parentID'] = $cat->parent;
            $this->aAllCats[$cat->term_id]['slug'] = $cat->slug;
            $this->aAllCats[$cat->term_id]['name'] = str_replace( '~', '&nbsp;', str_pad( ltrim( $prefix . ' ' . $cat->name ), 100, '~') );
        }    
        foreach ($into as $topCat) {
            $topCat->children = array();
            $this->sortCats($cats, $topCat->children, $topCat->term_id, $prefix );
        }
        if ( !$cats ){
            foreach ( $this->aAllCats as $ID => $aDetails ){
                if ( $aDetails['parentID'] ){
                    $this->aAllCats[$aDetails['parentID']]['children'][$ID] = $this->aAllCats[$ID];
                }
            }
        } 
    }

    public function cleanCats(){
        foreach ( $this->aAllCats as $ID => $aDetails ){
            if ( $aDetails['parentID'] ){
                unset( $this->aAllCats[$ID] );
            }
        }
    }

    public function getSlugNameCats(&$cats, &$into ){
        foreach ( $cats as $i => $cat ){
            $into[$cat['slug']] = $cat['name'];
            if ( isset( $cat['children'] ) ){
                $this->getSlugNameCats($cat['children'], $into );
            }
            unset( $cats[$i] );
        }
    }

    public function getCategories( $url, $shortname, $categories = '' ){
        $aRet = array();
        $aCategories = $this->getTaxonomies( $url, 'category', $categories );
        $this->setCategories( $aCategories, $shortname );
        $categories = get_terms( array(
            'taxonomy' => 'glossary_category',
            'meta_query' => array( array(
                'key' => 'source',
                'value' => $shortname
            ) ),
            'hide_empty' => FALSE
            ) );
        $categoryHierarchy = array();
        $this->sortCats($categories, $categoryHierarchy);
        $this->cleanCats();
        $this->getSlugNameCats( $this->aAllCats, $aRet );
        return $aRet;
    }


    public function deleteGlossary( $source ){
        // deletes all glossaries by source
        $iDel = 0;
        $allGlossaries = get_posts( array( 'post_type' => 'glossary', 'meta_key' => 'source', 'meta_value' => $source, 'numberposts' => -1 ) );

        foreach ( $allGlossaries as $glossary ) {
            wp_delete_post( $glossary->ID, TRUE );
            $iDel++;
        } 
        return $iDel;
    }

    protected function cleanContent( $txt ){
        // returns content without info below '<!-- rrze-glossary -->'
        $pos = strpos( $txt, '<!-- rrze-glossary -->' );
        
        if ($pos !== false){
            $txt = substr( $txt, 0, $pos);
        }

        return $txt;
    }

    protected function absoluteUrl( $txt, $baseUrl ){
        // converts relative URLs to absolute ones
        $needles = array('href="', 'src="', 'background="');
        $newTxt = '';
        if (substr( $baseUrl, -1 ) != '/' ){
            $baseUrl .= '/';
        } 
        $newBaseUrl = $baseUrl;
        $baseUrlParts = parse_url( $baseUrl );
        foreach ( $needles as $needle ){
            while( $pos = strpos( $txt, $needle ) ){
                $pos += strlen( $needle );
                if ( substr( $txt, $pos, 7 ) != 'http://' && substr( $txt, $pos, 8) != 'https://' && substr( $txt, $pos, 6) != 'ftp://' && substr( $txt, $pos, 7 ) != 'mailto:' ){
                    if ( substr( $txt, $pos, 1 ) == '/' ){
                        $newBaseUrl = $baseUrlParts['scheme'] . '://' . $baseUrlParts['host'];
                    }
                    $newTxt .= substr( $txt, 0, $pos ).$newBaseUrl;
                } else {
                    $newTxt .= substr( $txt, 0, $pos );
                }
                $txt = substr( $txt, $pos );
            }
            $txt = $newTxt . $txt;
            $newTxt = '';
        }
        // convert all elements of srcset, too
        $needle = 'srcset="';
        while( $pos = strpos( $txt, $needle, $pos ) ){
            $pos += strlen( $needle );
            $len = strpos( $txt, '"', $pos ) - $pos;
            $srcset = substr( $txt, $pos, $len );
            $aSrcset = explode( ',', $srcset );
            $aNewSrcset = array();
            foreach( $aSrcset as $src ){
                $src = trim( $src );
                if ( substr( $src, 0, 1 ) == '/' ){
                    $aNewSrcset[] = $newBaseUrl . $src;
                }                                
            }
            $newSrcset = implode( ', ', $aNewSrcset );
            $txt = str_replace( $srcset, $newSrcset, $txt );
        }
        return $txt;
      }

    protected function getGlossary( &$url, &$categories ){
        $glossarys = array();
        $aCategoryRelation = array();
        $filter = '&filter[glossary_category]=' . $categories;
        $page = 1;

        do {
            $request = wp_remote_get( $url . ENDPOINT . '?page=' . $page . $filter );
            $status_code = wp_remote_retrieve_response_code( $request );
            if ( $status_code == 200 ){
                $entries = json_decode( wp_remote_retrieve_body( $request ), true );
                if ( !empty( $entries ) ){
                    if ( !isset( $entries[0] ) ){
                        $entries = array( $entries );
                    }
                    foreach( $entries as $entry ){
                        if ( $entry['source'] == 'website' ){
                            $content = $this->cleanContent( $entry['content']['rendered'] );
                            $content = $this->absoluteUrl( $content, $url );

                            $glossarys[$entry['id']] = array(
                                'id' => $entry['id'],
                                'title' => $entry['title']['rendered'],
                                'content' => $content,
                                'lang' => $entry['lang'],
                                'glossary_category' => $entry['glossary_category'],
                                'remoteID' => $entry['remoteID'],
                                'remoteChanged' => $entry['remoteChanged']
                            );
                            $sTag = '';
                            foreach ( $entry['glossary_tag'] as $tag ){
                                $sTag .= $tag . ',';
                            }
                            $glossarys[$entry['id']]['glossary_tag'] = trim( $sTag, ',' );
                            $glossarys[$entry['id']]['URLhasSlider'] = ( ( strpos( $content, 'slider') !== false ) ? $entry['link'] : FALSE ); // we cannot handle sliders, see note in Shortcode.php shortcodeOutput()
                        }
                    }
                }
            }
            $page++;   
        } while ( ( $status_code == 200 ) && ( !empty( $entries ) ) );

        return $glossarys;
    }

    public function setTags( $terms, $shortname ){
        if ( $terms ){
            $aTerms = explode( ',', $terms );
            foreach( $aTerms as $name ){
                if ( $name ){
                    $term = term_exists( $name, 'glossary_tag' );
                    if ( !$term ) {
                        $term = wp_insert_term( $name, 'glossary_tag' );
                        update_term_meta( $term['term_id'], 'source', $shortname );    
                    }
                }
            }
        }
    }

    public function getGlossaryRemoteIDs( $source ){
        $aRet = array();
        $allGlossaries = get_posts( array( 'post_type' => 'glossary', 'meta_key' => 'source', 'meta_value' => $source, 'fields' => 'ids', 'numberposts' => -1 ) );
        foreach ( $allGlossaries as $postID ){
            $remoteID = get_post_meta( $postID, 'remoteID', TRUE );
            $remoteChanged = get_post_meta( $postID, 'remoteChanged', TRUE );
            $aRet[$remoteID] = array(
                'postID' => $postID,
                'remoteChanged' => $remoteChanged
                );
        }
        return $aRet;
    }

    public function setGlossary( $url, $categories, $shortname ){
        $iNew = 0;
        $iUpdated = 0;
        $iDeleted = 0;
        $aURLhasSlider = array();

        // get all remoteIDs of stored glossaries to this source ( key = remoteID, value = postID )
        $aRemoteIDs = $this->getGlossaryRemoteIDs( $shortname );

        // $this->deleteTags( $shortname );
        // $this->deleteCategories( $shortname );
        // $this->getCategories( $url, $shortname );

        // get all glossaries
        $aGlossary = $this->getGlossary( $url, $categories );
        
        // set glossaries
        foreach ( $aGlossary as $glossary ){
            $this->setTags( $glossary['glossary_tag'], $shortname );

            $aCategoryIDs = array();
            foreach ( $glossary['glossary_category'] as $name ){
                $term = get_term_by( 'name', $name, 'glossary_category' );
                $aCategoryIDs[] = $term->term_id;
            }

            if ( $glossary['URLhasSlider'] ) {
                $aURLhasSlider[] = $glossary['URLhasSlider'];
            } else {
                if ( isset( $aRemoteIDs[$glossary['remoteID']] ) ) {
                    if ( $aRemoteIDs[$glossary['remoteID']]['remoteChanged'] < $glossary['remoteChanged'] ){
                        // update glossary
                        $post_id = wp_update_post( array(
                            'ID' => $aRemoteIDs[$glossary['remoteID']]['postID'],
                            'post_name' => sanitize_title( $glossary['title'] ),
                            'post_title' => $glossary['title'],
                            'post_content' => $glossary['content'],
                            'meta_input' => array(
                                'source' => $shortname,
                                'lang' => $glossary['lang'],
                                'remoteID' => $glossary['remoteID']
                                ),
                            'tax_input' => array(
                                'glossary_category' => $aCategoryIDs,
                                'glossary_tag' => $glossary['glossary_tag']
                                )
                            ) ); 
                        $iUpdated++;
                    }
                    unset( $aRemoteIDs[$glossary['remoteID']] );
                } else {
                    // insert glossary
                    $post_id = wp_insert_post( array(
                        'post_type' => 'glossary',
                        'post_name' => sanitize_title( $glossary['title'] ),
                        'post_title' => $glossary['title'],
                        'post_content' => $glossary['content'],
                        'comment_status' => 'closed',
                        'ping_status' => 'closed',
                        'post_status' => 'publish',
                        'meta_input' => array(
                            'source' => $shortname,
                            'lang' => $glossary['lang'],
                            'remoteID' => $glossary['id'],
                            'remoteChanged' => $glossary['remoteChanged'],
                            'sortfield' => ''
                            ),
                        'tax_input' => array(
                            'glossary_category' => $aCategoryIDs,
                            'glossary_tag' => $glossary['glossary_tag']
                            )
                        ) );
                    $iNew++;
                }
            }
        }

        // delete all other glossaries to this source
        foreach( $aRemoteIDs as $remoteID => $aDetails ){
            wp_delete_post( $aDetails['postID'], TRUE );
            $iDeleted++;
        }

        return array( 
            'iNew' => $iNew,
            'iUpdated' => $iUpdated,
            'iDeleted' => $iDeleted,
            'URLhasSlider' => $aURLhasSlider
        );
    }
}    


