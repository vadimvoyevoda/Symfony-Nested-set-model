<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends Controller
{
	/**
     * @Route("/products", name="all_products")
     */
    public function show_products()
    {
    	$repository = $this->getDoctrine()->getRepository(Product::class);
    	$products = $repository->findAll();

        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
            'products'	=> $products
        ]);
    }

    /**
     * @Route("/product/{id}", name="product_show")
     */
    public function show_product($id, CategoryController $category)
    {
    	if( !empty($id) ){
	    	
    		$repository = $this->getDoctrine()->getRepository(Product::class);
	    	$product = $repository->find($id);

		    if (!$product) {
		        throw $this->createNotFoundException(
		            'No product found for id '.$id
		        );
		    }

		    $product_categories = $category->getProductCategories($id);
		    
		    return $this->render('product/product.html.twig', [
	            'controller_name' => 'ProductController',
	            'product'	=> $product,
	            'product_categories' => $product_categories
	        ]);
		}

    }
}
