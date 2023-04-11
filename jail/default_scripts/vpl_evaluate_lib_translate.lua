local dirEvaluate = "."

local lib_lua = require "./vpl_evaluate_lib_utils"

local evaluate = {}

local function jsonFileGet(filename)
  return lib_lua.parse(lib_lua.readall(filename))
end

function loadTransLangLib(lang)
  evaluate["en"] = jsonFileGet(dirEvaluate .. "en.json")
  local filename = dirEvaluate .. lang .. ".json"
  if (lib_lua.file_exists(filename)) then
    evaluate[lang] = jsonFileGet(filename)
  else
    evaluate[lang] = evaluate["en"]
  end
end

function langEvaluate(lang, id)
  return evaluate[lang][id] and evaluate[lang][id] or evaluate["en"][id]
end