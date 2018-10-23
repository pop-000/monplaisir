<?php
if( !function_exists('telephone_link') )
{
	function telephone_link(){
		$file = $_SERVER["DOCUMENT_ROOT"] . '/include/telephone.php';
		if( file_exists( $file ) )
		{
			$phone = file_get_contents( $file );
		}
		if(strlen($phone) > 6)
		{
			$remove = array(
				'(', 
				')', 
				' ', 
				'-'
				);
			$str = str_replace($remove, '', $phone);
			return '<a href="tel:' . $str . '">' . $phone . '</a>';
		}
	}
}
if( empty($telephone_link) )
{
	$telephone_link = telephone_link();
}
echo $telephone_link;
?>