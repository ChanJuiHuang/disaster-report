const checkBox = function (pattern, isCheck) {
  let inputs = document.getElementsByTagName('input')
  let reg = null
  for (let index = 0; index < inputs.length; index++) {
    reg = new RegExp(pattern, 'g')
    if (reg.test(inputs[index].id)) {
      inputs[index].checked = isCheck
    }
  }
}

let checkTeams = document.getElementsByClassName('checkTeams')
let unCheckTeams = document.getElementsByClassName('unCheckTeams')
let teams = ['A', 'B', 'C', 'D', 'E']
for (let index = 0; index < checkTeams.length; index++) {
  checkTeams[index].addEventListener('click', function (event) {
    checkBox(teams[index], true)
    event.preventDefault()
  })
  unCheckTeams[index].addEventListener('click', function (event) {
    checkBox(teams[index], false)
    event.preventDefault()
  })
}