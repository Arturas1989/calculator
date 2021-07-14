
const script = document.querySelector('#script');
const getOrder = script.dataset.getorder;

let timeout = null
console.log(getOrder);




window.addEventListener('load',function(event)
{
    console.log('1');
    DOM = document.querySelector('.data');

        
        DOM.addEventListener('input',function (e) 
        {
            if(e.target && e.target.matches(".code")){
                clearTimeout(timeout);
            console.log(timeout);

            timeout = setTimeout(function () 
            {
                axios.get(getOrder)
                .then(response =>
                {
                    const row = response.data.find(function(data) 
                    {
                        if(data.code === e.target.value)
                        {
                            return true;
                        }  
                    });

                    const targetName = e.target.name;
                    const targetLength = e.target.name.length;
                    const endNumText = targetName.substring(5,targetLength);
                    const width = '#width-' + endNumText;
                    const length = '#length-' + endNumText;
                    const from_sheet_count = '#from_sheet_count-' + endNumText;

                    if(row)
                    {
                        document.querySelector(width).innerText = row.sheet_width;
                        document.querySelector(length).innerText = row.sheet_length;
                        document.querySelector(from_sheet_count).innerText = row.from_sheet_count;
                    }

                    else
                    {
                        document.querySelector(width).innerText = 'Nerasta';
                        document.querySelector(length).innerText = 'Nerasta';
                        document.querySelector(from_sheet_count).innerText = 'Nerasta';
                    }

                })
            }, 1000);
            }
            

            
        });
        
    
    
});
