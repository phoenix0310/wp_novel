<?php

    /**
 	 * Helper class on zip validation
     */

    class MADARA_ZIP_VALIDATION{

        static function is_zip_valid( $zip_file, $chapter_type ){

            $zip = self::get_zip_structure( $zip_file, $chapter_type );
			
			if(is_wp_error($zip)){
				return array(
                    'is_valid' => false,
                    'message' => $zip->get_error_message(),
                    'data' => $zip
                );
			}
			
            $is_valid = false;

            $chapter_type = $chapter_type == 'text' || $chapter_type == 'video' ? 'content' : 'manga';

            if( $chapter_type == $zip['chapter_type'] ){
                $is_valid = true;
            }

            if( ! $is_valid ){
                return array(
                    'is_valid' => false,
                    'message' => esc_html__("This Zip file isn't valid for this chapter type"),
                    'data' => $zip
                );
            }

            return array(
                'is_valid' => true,
                'data' => $zip,
            );

        }

        //this function would read the zip file to manga structure : volume > chapter > chapter file
        static function get_zip_structure( $zip_file, $type = '' ){
            $zip = new ZipArchive;
            $res = $zip->open( $zip_file );
            if($res === true){
                $zip_structure = array();
                
                $number_of_dirs = 0;
                
                $index = 0;

                while( $zip_entry = $zip->statIndex( $index ) ){
                    $index++;
                    
                    $entry_name = $zip_entry['name'];

                    //check if this is a dir or a file
                    $is_zip_dir = self::is_zip_dir( $entry_name );
                    //if this zip hasn't defined if it's a single chapter zip or it was a single chapter zip
                    if( !isset( $is_single_chapter ) || $is_single_chapter ){

                        //check if this entry is a file
                        $extension = self::get_file_extension( $entry_name );
                        
                        if( !$is_zip_dir && $extension ){ //if it's a file, it's would be a manga single chapter zip file
                            $zip_structure[] = array(
                                'single_chapter_file' => basename( $entry_name ),
                                'extension'           => $extension
                            );
                            $is_single_chapter = true;

                            //get chapter type for single chapter zip file
                            $images_extensions = WP_MANGA_FUNCTIONS::get_validated_image_extensions();

                            if( in_array( $extension, $images_extensions ) ){
                                $chapter_type = 'manga';
                            } elseif( $extension == 'txt' ){
                                $chapter_type = 'content';
                            } elseif( $extension == 'ds_store' ){
                                continue;
                            } elseif( $extension == '__MACOSX' ){
                                continue;
                            } else {
                                return new WP_Error( 'invalid_file', __( 'Invalid Zip file, file contains unwanted file. Please check agains', 'madara' ) );
                            }

                        } else {
                            if($entry_name == '__MACOSX/'){
                                // ingore MACOS hidden folder
                                continue;
                            }
                            
                            $is_single_chapter = false;
                        }

                        if( $is_single_chapter ){
                            continue;
                        }
                    } else {
                        $is_single_chapter = false;
                    }

                    //trim the / on dir name
                    $entry_name = trim($entry_name, '/');

                    //if it's not single_chapter file type
                    $parts = explode('/', $entry_name);
                    if( !$is_zip_dir ){ //if it's file
                        //by this, file path will locate at key 0
                        $parts = array_reverse( $parts );

                        $extension = self::get_file_extension( $parts[0], $entry_name );
                        $images_extensions = WP_MANGA_FUNCTIONS::get_validated_image_extensions();
                        
                        if( in_array( $extension, $images_extensions ) ){
                            $chapter_type = 'manga';
                        }elseif( $extension == 'txt' ){
                            $chapter_type = 'content';
                        }elseif( $extension == 'ds_store' ){
                            continue;
                        }elseif( $extension == '__MACOSX' ){
                            continue;
                        } else {
                            return new WP_Error( 'invalid_file', __( 'Invalid Zip file, file contains unwanted file. Please check again', 'madara' ) );
                        }

                        if( count( $parts ) == 3 ){
                            $zip_structure[$parts[2]][$parts[1]][] = array(
                                'file' => $parts[0],
                                'extension' => $extension
                            );
                            $zip_type = 'multi_chapters_with_volumes';
                        }elseif( count( $parts ) == 2 ){
                            $zip_structure[$parts[1]][] = array(
                                'file' => $parts[0],
                                'extension' => $extension
                            );
                        }
                    } else {
                        $number_of_dirs++;
                    }
                }

                $zip->close();

                /*
                *   There will be 3 types of zip file
                *       - single_chapter : only contains images file
                *       - multi_chapters_with_volumes : contains volumes -> chapters -> images file
                *       - multi_chapters_no_volume : contains chapters -> images file
                */
                
                if( $is_single_chapter ){
                    $zip_type = 'single_chapter';
                    $chapter_type = 'manga';
                }elseif( !isset( $zip_type ) ){
                    if($number_of_dirs == 1){
                        $zip_type = 'single_chapter';
                        $chapter_type = 'manga';
                    } else {
                        $zip_type = 'multi_chapters_no_volume';
                    }
                }

                return array(
                    'zip_type'     => $zip_type,
                    'chapter_type' => $chapter_type,
                    'data'         => $zip_structure
                );
            } else {
                // .zip is corrupted
                return new WP_Error( 'corrupted_file', esc_html__( '.zip file is corrupted. Please check again', 'madara' ) );
            }
        }

        static function get_file_extension( $filename, $entry_name = '' ){

            $pathinfo = pathinfo( $filename );
            
            if(strpos($entry_name, '__MACOSX') !== false){
                return '__MACOSX';
            }

            if( isset( $pathinfo['extension'] ) ){
                return strtolower( $pathinfo['extension'] );
            }

            return false;

        }

        static function is_zip_dir( $filename ){
            if(strpos($filename,'__MACOSX') !== false){
                return false;
            } else {
                return substr( $filename, -1 ) === '/';
            }
        }

    }

    $madara_zip_validation = new MADARA_ZIP_VALIDATION();
