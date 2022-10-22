const navigation = () => {
    let elements = document.getElementsByClassName("navigable");
    let currentIndex = 0;

    document.onkeydown = function(e) {
        if ((e.target.className).includes('navigable')) {
            switch (e.code) {
                case 'ArrowUp':
                    currentIndex = (currentIndex == 0) ? elements.length - 1 : --currentIndex;
                    elements[currentIndex].focus();
                    break;
                case 'ArrowDown':
                case 'Enter':
                case 'NumpadEnter':
                    currentIndex = ((currentIndex + 1) == elements.length) ? 0 : ++currentIndex;
                    elements[currentIndex].focus();
                    break;
            }
        }
    };

    document.onclick = function(e) {
        if (typeof  e.target.className === 'string' && (e.target.className).includes('navigable')){
            let el = e.target;
            let index = Number(el.getAttribute('data-i'));
            currentIndex = index * 2;
            if((e.target.className).includes('nav-cantidad')){
                currentIndex = currentIndex+1;
            }
        }
    };
};

