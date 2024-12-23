// document.addEventListener('DOMContentLoaded', function () {
//     //const input = document.getElementById('products_image'); // Check that the ID is correct
//     const input = document.getElementsByClassName('input-image');
//     if (input) {
//         input.addEventListener('change', function (event) {
//             const file = event.target.files[0];
//             //const preview = document.getElementById('imagePreview'); // Make sure this ID exists on Twig
//             const preview = document.getElementsByClassName('imagePreview');
//             if (file) {
//                 const reader = new FileReader();
//                 reader.onload = function (e) {
//                     preview.src = e.target.result; // Update the image src
//                     //preview.style.display = 'block'; // Mostra a imagem
//                     preview.style.width = '100%';
//                 };
//                 reader.readAsDataURL(file); // Reads the file as a URL
//             } else {
//                 preview.src = '#'; // Remove src if there is no file
//                 //preview.style.display = 'none'; // Hide the image
//             }
//         });
//     } else {
//         console.error('Element with ID “imageUpload” was not found.');
//     }
// });
document.addEventListener('DOMContentLoaded', function () {
    const inputs = document.querySelectorAll('.input-image'); // Seleciona todos os inputs com a classe 'input-image'

    inputs.forEach(function (input) {
        input.addEventListener('change', function (event) {
            const file = event.target.files[0];
            const previewId = input.getAttribute('data-preview'); // Obtém o ID da pré-visualização associado
            const preview = document.getElementById(previewId); // Seleciona a pré-visualização correta

            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    if (preview) {
                        preview.src = e.target.result; // Atualiza o `src` da imagem
                        preview.style.display = 'block'; // Exibe a imagem
                        preview.style.width = '100%'; // Ajusta a largura
                    }
                };
                reader.readAsDataURL(file); // Lê o arquivo como URL
            } else if (preview) {
                preview.src = ''; // Limpa o `src` se não houver arquivo
                preview.style.display = 'none'; // Esconde a imagem
            }
        });
    });
});


