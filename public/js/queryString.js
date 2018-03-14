function getQueryStrings() {
  let queries = []
  let query = null
  let _queries = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&')
  for (let i = 0; i < _queries.length; i++) {
    query = _queries[i].split('=')
    queries[query[0]] = query[1]
  }
  return queries
}

let queries = getQueryStrings()

if (queries['fail'] === '7') {
  alert('伺服器連線錯誤，請聯絡管理員或嘗試重新登入！')
} else if (queries['fail'] === '1') {
  alert('您的帳號或密碼輸入錯誤！')
} else if (queries['fail'] === '2') {
  alert('您的驗證碼輸入錯誤！')
}

history.pushState('', '', '/disaster_report/index.php')