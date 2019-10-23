<?php

namespace App\Controller;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    /**
     * @Route("/categories", name="categories")
     */
    public function index()
    {	
    	$categories = $this->getCategories();

        return $this->render('category/index.html.twig', [
            'controller_name' => 'CategoryController',
            'categories' => $categories
        ]);
    }

    /**
     * @Route("/categories_total", name="categories_total")
     */
    public function categories_total()
    {	
    	$categories = $this->getCategoriesWithProductsCount();

        return $this->render('category/category_total.html.twig', [
            'controller_name' => 'CategoryController',
            'categories' => $categories
        ]);
    }

    //Listing 1
    public function getCategories(){

    	$em = $this->getDoctrine()->getManager();
    	$conn = $em ->getConnection();

        $sql = "SELECT category.*, COUNT(product.product_id) product_count FROM category LEFT JOIN product ON product.category_id = category.id GROUP BY category.name ORDER BY category.lft";
        $sth = $conn->prepare($sql);
        $sth->execute();
        $db_categories = $sth->fetchAll();

        $sth = null;

        $categories  = array();
		for ($i=0; $i < count($db_categories); $i++) { 

			$level = 0;
			$has_children = $db_categories[$i]['rgt'] - $db_categories[$i]['lft'] > 1;

			for ($j = 0; $j < $i; $j++) { 
				
				if( $db_categories[$i]['lft'] > $db_categories[$j]['lft'] 
					&& $db_categories[$i]['rgt'] < $db_categories[$j]['rgt'] ){

					$level++;

				}

			}
			
			$categories[ $db_categories[$i]['id'] ] = array(
				"name" 	=> $db_categories[$i]['name'],
				"level" => $level,
				"has_children" => $has_children,
			);

		}

		return $categories;
    }

    //Listing 2
    public function getProductCategories( int $product_id ){

    	$product_cats = array();

    	if( !empty($product_id) ){
	    	
	    	$em = $this->getDoctrine()->getManager();
	    	$conn = $em ->getConnection();

	    	$prod_cat_sql = "SELECT category.* FROM product INNER JOIN category ON category.id = product.category_id  WHERE product_id = :product_id";
	    	$sth = $conn->prepare($prod_cat_sql);
			$sth->execute( array(
					":product_id" => $product_id,
				) 
			);
			$product_cat = $sth->fetch();

			if( !empty($product_cat) ){

				$sql = "SELECT * FROM category WHERE lft <= :prod_cat_lft AND rgt >= :prod_cat_rgt ORDER BY lft";
				$sth = $conn->prepare($sql);
				$sth->execute( array(
						":prod_cat_lft" => $product_cat['lft'],
						":prod_cat_rgt" => $product_cat['rgt']
					) 
				);
				$product_cats = $sth->fetchAll();
			}

			$sth = null;
		
		}

		return $product_cats;
    }

    //Listing 3
    public function getCategoriesWithProductsCount(){

    	$em = $this->getDoctrine()->getManager();
    	$conn = $em ->getConnection();

    	$sql = "SELECT category.*, COUNT(product.product_id) product_count FROM category LEFT JOIN product ON product.category_id = category.id GROUP BY category.name ORDER BY category.lft";
        $sth = $conn->query($sql);
		$db_categories = $sth->fetchAll();

		$sth = null;

		$categories  = array();
		foreach ($db_categories as $category) {
			
			$categories[ $category['id'] ] = array(
				"name" 			=> $category['name'],
				"product_count" => $category['product_count']
			);

			foreach ($db_categories as $inner_category) {
				
				if( $inner_category['lft'] > $category['lft'] 
					&& $inner_category['rgt'] < $category['rgt'] ){

					$categories[ $category['id'] ]['product_count'] += $inner_category['product_count'];

				}

			}

		}

		return $categories;
    }

}
