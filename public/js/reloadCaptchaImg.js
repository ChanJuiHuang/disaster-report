(function () {
  let reloadImg = function (dImg) {
    dImg.src = dImg.src
  }
  let dReloadLink = document.getElementById("reload-captcha")
  let dImg = document.getElementById("captcha-img")

  dReloadLink.onclick = () => reloadImg(dImg)
})()