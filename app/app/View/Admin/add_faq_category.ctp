<?php $data=isset($faq_cat_data)?$faq_cat_data:""; 
      // print_r($data);
       @ extract($data['Faq_category']);
        @ extract($data);
        $lang_id=isset($_GET['lang_id'])?$_GET['lang_id']:'1';
        if(isset($Faq_category_lang)){
        foreach($Faq_category_lang as $val){
          if($val['lang_id']==$lang_id)
          {
            $Faq_category_lang1=$val;
          }
        }
      }
        @$cid=$id;
        unset($id);
        @extract($Faq_category_lang1);
    ?>
        <?=$this->Session->flash('bad')?> 
        <?=$this->Session->flash('msg')?>
 <article class="module width_full">
      <header><h3><?=$admin_button?> FAQ Category</h3>

      <a href="<?=$this->webroot?>admin/faq_category_manager" class="heading_link">View category Lists</a>
      <a href="<?=$this->webroot?>admin/faq_manager" class="heading_link">View FAQ Lists</a>
      </header>
     
        <div class="module_content">       
          <div id="stylized" class="myform" >
            <?php if(isset($cid)){ ?>
        <form>
           <table>
                  <tr>
                    <td>
                   <label>Choose Language
                     <span class="small">Select Language</span>
                   </label>
                    </td>
                    <td>
                   

                      <select name="lang_id" onchange="this.form.submit();" style="width: 185px;">
                        <?php 
                        foreach($lang as $val){ ?>
                        <option value="<?=$val['Language']['id']?>" <?php echo $this->Template->Select($val['Language']['id'],empty($lang_id)?1:$lang_id);?>><?=$val['Language']['lang_name']?> (<?=$val['Language']['lang_short_name']?>)</option>
                       <?php } 
                        ?>
                      </select>
                      </td>
                  </tr>
                </table>
              </form>
         <?php } else{
         ?>
            <form>
                <table>
                    <tr>
                      <td>
                     <label>Choose Language
                       <span class="small">Select Language</span>
                     </label>
                      </td>
                      <td>
             <select name="lang_id" disabled="disabled" onchange="this.form.submit();" style="width: 185px;">
                <option value="1" readonly="readonly">English</option>
             </select>
              </td>
                    </tr>
                  </table>
                </form>
         <?php
          
         }
            ?>
              <form method="post" name="Image" enctype="multipart/form-data" action=""> 
                <input type="hidden" name="lang_id" value="<?=$lang_id?>"/>
                 <input type="hidden" name="cat_lang_id" value="<?=@$id?>"/>
                <table>
                  <tr>
                    <td>
                   <label>Category Name
                     <span class="small">Add Category Name</span>
                   </label>
                    </td>
                    <td>
                    <input type="text" required name="category_name" placeholder="Enter category." value="<?=isset($category_name)?$category_name:""?>">
                  </td>
                 </tr>
                   <tr>
                    <td>
                    <label>Parent:
                     <span class="small">Choose category Parent</span>
                   </label>
                   </td>
                    <td>
                        <?php //print_r($faq_cat_names)?>
                     <select name="parent_id" requred>
                      <option value="">Choose Parent</option>
                      <?php 
                      foreach ($faq_cat_names as $key => $value) { ?>

                      <?php

                       if(isset($cid) and ($cid==$value['Faq_category']['id'] or $cid==$value['Faq_category']['parent_id']) )
                            continue;
                        ?>
                        <option value="<?=$value['Faq_category']['id']?>" <?php echo $this->Template->Select($value['Faq_category']['id'],isset($parent_id)?$parent_id:"");?>><?=$value['Faq_category_lang']['category_name']?></option>
                    <?php  } 
                      ?>
                     
                    </select>
                    </td>                 
                    </tr>
                   
                     
                  <tr>
                    <td>
                    <label>status
                     <span class="small">Choose Status</span>
                     </label>
                   </td>
                   <td>
                    <select name="status" requred>
                      <option value="1" <?php if(isset($status) && $status==1){ echo "selected='selected'";} ?>>Active</option>
                      <option value="0" <?php if(isset($status) && $status==0){ echo "selected='selected'";} ?>>Inactive</option>
                    </select>
                  </td>
                 </tr>
                 <tr>
                  <td></td>
                    <td>
            <button type="submit" name=""><?=$admin_button?></button>
                  </td>
                 </tr>            
               </form>            
           <div class="clear"></div>
        </div>
      
       
     </div>
    </article><!-- end of post new article -->

