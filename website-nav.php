
<div id="heade_tool">

    <div id="left_tools">
        <a href="website-categories">Catégories</a>
        <a href="website">Produits</a>
        <a href="website-portes">Portes d'entrée</a>

    </div>

    <div id="right_tools">
        
        <?php if ($_GET['page'] == 'website-porte') { ?>
        
            <?php if (!empty($prev_produit['id'])) { ?>
            <a href="/website-porte?id=<?php echo $prev_produit['id'] ?>"><< <?php echo $prev_produit['name'] ?></a>
            <?php } ?>
            
            <?php if (!empty($next_produit['id'])) { ?>
            <a href="/website-porte?id=<?php echo $next_produit['id'] ?>"><?php echo $next_produit['name'] ?> >></a>
            <?php } ?>
            
        <?php } else { ?>
        
            <?php if (!empty($prev_produit['id'])) { ?>
            <a href="/website-produit?id=<?php echo $prev_produit['id'] ?>"><< <?php echo $prev_produit['name'] ?></a>
            <?php } ?>
            
            <?php if (!empty($next_produit['id'])) { ?>
            <a href="/website-produit?id=<?php echo $next_produit['id'] ?>"><?php echo $next_produit['name'] ?> >></a>
            <?php } ?>
        
        <?php } ?>
        
    </div>
    
</div>
