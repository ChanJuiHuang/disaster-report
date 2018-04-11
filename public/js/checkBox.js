const checkBox = function (pattern, option) {
  let inputs = document.getElementsByTagName('input')
  let reg = null
  for (let index = 0; index < inputs.length; index++) {
    reg = new RegExp(pattern, 'g')
    if (reg.test(inputs[index].id)) {
      if (option === 'checkAll') {
        inputs[index].checked = true
      } else if (option === 'reverseCheck') {
        inputs[index].checked = !inputs[index].checked
      }
    }
  }
}

let checkTeams = document.getElementsByClassName('checkTeams')
let unCheckTeams = document.getElementsByClassName('unCheckTeams')
let teams = ['A', 'B', 'C', 'D', 'E']
for (let index = 0; index < checkTeams.length; index++) {
  checkTeams[index].addEventListener('click', function (event) {
    checkBox(teams[index], 'checkAll')
    event.preventDefault()
  })
  unCheckTeams[index].addEventListener('click', function (event) {
    checkBox(teams[index], 'reverseCheck')
    event.preventDefault()
  })
}