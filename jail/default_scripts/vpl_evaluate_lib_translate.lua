local lib_lua = require "./vpl_evaluate_lib_utils"

local evaluate = {}

local function jsonFileGet(filename)
  return lib_lua.parse(lib_lua.readall(filename))
end

function loadTransLangLib(idiom)
  evaluate["en"] = jsonFileGet("vpl_evaluate_en.json")
  local filename = "vpl_evaluate_" .. idiom .. ".json"
  if (lib_lua.file_exists(filename)) then
    evaluate[idiom] = jsonFileGet(filename)
  else
    evaluate[idiom] = evaluate["en"]
  end
end

function langEvaluate(idiom, id)
  return evaluate[idiom][id] and evaluate[idiom][id] or evaluate["en"][id]
end