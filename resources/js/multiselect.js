


window.onload=function() 
{
    $('#boards').multiSelect
    ({
        selectableHeader: "<div class='custom-header'>Gofrų sąrašas</div>",
        selectionHeader: "<div class='custom-header'>Pasirinkta</div>",
    });

    $('#marks').multiSelect
    ({
        selectableHeader: "<input type='text' class='search-input' autocomplete='off' placeholder='Ieškoti'>",
        selectionHeader: "<input type='text' class='search-input' autocomplete='off' placeholder='Ieškoti'>",
        afterInit: function(ms)
        {
            let that = this,
            $selectableSearch = that.$selectableUl.prev(),
            $selectionSearch = that.$selectionUl.prev(),
            selectableSearchString = '#'+that.$container.attr('id')+' .ms-elem-selectable:not(.ms-selected)',
            selectionSearchString = '#'+that.$container.attr('id')+' .ms-elem-selection.ms-selected';

            that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
            .on('keydown', function(e)
            {
                if (e.which === 40){
                    that.$selectableUl.focus();
                    return false;
                }
            });

            that.qs2 = $selectionSearch.quicksearch(selectionSearchString)
            .on('keydown', function(e)
            {
                if (e.which == 40){
                    that.$selectionUl.focus();
                    return false;
                }
            });
        },

        afterSelect: function()
        {
            this.qs1.cache();
            this.qs2.cache();
        },

        afterDeselect: function()
        {
            this.qs1.cache();
            this.qs2.cache();
        }
    });
}



// window.onload=function(){
//     const DOM = document.querySelector(".multiple");
//     DOM.onscroll = function(e){
//         DOM.addEventListener('click', function (e){
//             if(e.target){
                
//                 const scrollTop = e.pageY;
//                 const scrollLeft = e.pageX;
//                 DOM.scrollTo(scrollLeft, scrollTop);
//             }
            
//         })
        
//     }
//     DOM.addEventListener('mousedown',function(e){
        
//         option = DOM.getElementsByTagName('option')[e.target.id]
//         if(option){
//             e.preventDefault();
//             if(option.selected){
//                 option.selected ='';
//             }
//             else{
//                 option.selected = 'selected';
//                 e.preventDefault();
                
                
//             }
            
//             // console.log(this.selectedIndex);
            
//             // e.target.addEventListener('onFocus',function(e){

//             // });
//         };
        
//     })
//     DOM.addEventListener('click',function (){
//         let result = "";
//         for(let i = 0; i<DOM.length; ++i){
//             let currentOption = DOM[i];
//             if(currentOption.selected){
//                 result += currentOption.value + ' ';
//             }
//         }
//         document.querySelector(".output").insertAdjacentHTML('beforeend',result);
//     })
    
    
// }
