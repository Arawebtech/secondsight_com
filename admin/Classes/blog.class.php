<?php
class blog
{
	function getBlog(){
		global $conn;
		$query = "select * from  blog ORDER BY id";
		$result = mysqli_query($conn, $query);
		return $result;
	}
			
 function addBlog($objusers){
	 global $conn;
		$query ="INSERT INTO  blog set ";
		foreach ($objusers as $key=>$value){
			$query.= "$key='$value'";
			$query.= ",";
		}  
	
		$query= substr($query, 0, -1);
		//echo $query;
		$result=mysqli_query($conn, $query);
		$last_id = mysqli_insert_id($conn);
		return $last_id;
	}
	
	
	function editBlog($objusers,$id){
		global $conn;
		$query =" UPDATE blog set ";
		foreach ($objusers as $key=>$value){
			$query .= "$key='$value'";
				$query.= ",";
		}
		
		$query= substr($query, 0, -1);
		$query .= " WHERE  id='$id'";		
		//echo $query;
		$result=mysqli_query($conn, $query);
		return $result;
	}
	
	function getBloginfo($id)
	   {
  	    global $conn;

		$query = "SELECT * FROM blog WHERE id ='$id'";
        $result = mysqli_query($conn, $query);
		return $result;
	   }	
	function deleteBlog($id)
	{
  	     global $conn;

	    $query  = "DELETE FROM blog WHERE id ='$id' ";
		$result=mysqli_query($conn, $query);
		return $result;
	}	
	
	
}

?>