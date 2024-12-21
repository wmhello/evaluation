<!--
 * @Author: wmhello 871228582@qq.com
 * @Date: 2021-11-27 17:30:20
 * @LastEditors: wmhello 871228582@qq.com
 * @LastEditTime: 2022-08-17 11:40:33
 * @FilePath: \element\src\views\template\simple.vue
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
-->
<template>
  <div class="warpper">
    <div class="toolbar">
      <el-form :inline="true" :model="searchForm" class="demo-form-inline">
        <el-form-item label="测评专题">
          <el-select v-model="searchForm.group_id" placeholder="请选择测试专题">
            <el-option
              v-for="(item, index) in group"
              :key="index"
              :label="item.group_name"
              :value="item.id"
            ></el-option>
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button @click="find()" plain>查询</el-button>
          <el-button type="info" @click="findReset()" plain>重置</el-button>
          <el-button
            @click="exportDataEvent"
            v-if="searchForm.group_id"
            style="margin-left:10px"
            type="primary"
            round
            >导出数据</el-button
          >
          <el-dropdown
            split-button
            type="success"
            @click="handleClick"
            v-if="showTable"
            @command="cmdSetGrade"
          >
            快速打分..
            <el-dropdown-menu slot="dropdown">
              <el-dropdown-item command="all-table-optimal"
                >整表全优</el-dropdown-item
              >
              <el-dropdown-item command="all-table-good"
                >整表全良</el-dropdown-item
              >
              <el-dropdown-item command="all-table-in"
                >整表全中</el-dropdown-item
              >
              <el-dropdown-item command="all-table-poor"
                >整表全差</el-dropdown-item
              >
            </el-dropdown-menu>
          </el-dropdown>
        </el-form-item>
      </el-form>
    </div>
    <div class="table">
      <vxe-table
        v-if="showTable"
        :menu-config="tableMenu"
        @menu-click="contextMenuClickEvent"
        :merge-cells="mergeCells"
        class="mytable-style"
        border
        resizable
        ref="xTable"
        height="700"
        align="center"
        @cell-dblclick="inputGrade"
        :print-config="{ isMerge: true }"
        :column-config="{ width: 90 }"
        :cell-class-name="cellClassName"
        :data="tableData"
        :footer-method="footerMethod1"
        show-footer
      >
        <vxe-column
          field="content_title"
          title="内容"
          flex="right"
        ></vxe-column>
        <vxe-column field="grade_name" title="等级" flex="right"></vxe-column>
        <vxe-column field="grade_value" title="分值" flex="right"></vxe-column>
        <vxe-colgroup :title="groupTitle">
          <vxe-column
            v-for="(item, index) in person"
            :key="index"
            :field="'name' + item.order"
            :title="item.name"
          ></vxe-column>
        </vxe-colgroup>
      </vxe-table>
    </div>
    <el-drawer
      title="其他评分表"
      :visible.sync="drawer"
      direction="rtl"
      size="360px"
    >
      <el-table :data="listData" height="950" border @cell-dblclick="getData">
        <el-table-column label="评分表编号" width="180">
          <template v-slot="{ row }">
            第{{ row.mark }}张
          </template>
        </el-table-column>
      </el-table>
    </el-drawer>

    <el-dialog title="批量打分" :visible.sync="dialogVisible" width="360">
      <el-form :model="formData" v-if="dialogVisible">
        <el-form-item label="测评表数量">
          <el-input-number v-model="formData.number" :min="1" autocomplete="off"></el-input-number>
        </el-form-item>
        <el-form-item label="等级">
          <el-select v-model="formData.grade">
            <el-option label="优" value="优"></el-option>
            <el-option label="良" value="良"></el-option>
            <el-option label="中" value="中"></el-option>
            <el-option label="差" value="差"></el-option>
          </el-select>
        </el-form-item>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogVisible = false">取 消</el-button>
        <el-button type="primary" @click="allSave">确 定</el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import "@/styles/view.scss";
import { getContent, getPerson, getMark, getGroup } from "@/api/common";
import { store, getContentByMark } from "@/api/grade";
import { SearchModel } from "@/model/grade";
export default {
  name: "GradeIndex",
  data() {
    return {
      module: "grade",
      newTitle: "新增信息",
      editTitle: "编辑信息",
      group_id: null,
      drawer: false,
      listData: [],
      formData: {
        grade: "",
        number: 1
      },
      searchForm: new SearchModel(),
      mergeCells: [
        { row: 0, col: 0, rowspan: 4, colspan: 1 },
        { row: 4, col: 0, rowspan: 4, colspan: 1 },
        { row: 8, col: 0, rowspan: 4, colspan: 1 },
        { row: 12, col: 0, rowspan: 4, colspan: 1 },
        { row: 16, col: 0, rowspan: 4, colspan: 1 }
      ],
      content: [],
      person: [],
      showTable: false,
      score: {},
      mark: null,
      tableMenu: {
        header: {
          options: [
            [{ code: "showTable", name: "显示评分表", disabled: false }]
          ]
        },
        body: {
          options: [
            [
              { code: "all-optimal", name: "全优", disabled: false },
              { code: "all-good", name: "全良", disabled: false },
              { code: "all-in", name: "全中", disabled: false },
              { code: "all-poor", name: "全差", disabled: false }
            ],
            [
              {
                name: "整表打分",
                disabled: false,
                children: [
                  { code: "all-table-optimal", name: "全优" },
                  { code: "all-table-good", name: "全良" },
                  { code: "all-table-in", name: "全中" },
                  { code: "all-table-poor", name: "全差" }
                ]
              },
              {
                name: "整行打分",
                disabled: false,
                children: [
                  { code: "all-line-optimal", name: "全优" },
                  { code: "all-line-good", name: "全良" },
                  { code: "all-line-in", name: "全中" },
                  { code: "all-line-poor", name: "全差" }
                ]
              }
            ],
            [
              { code: "optimal", name: "优", disabled: false },
              { code: "good", name: "良", disabled: false },
              { code: "in", name: "中", disabled: false },
              { code: "poor", name: "差", disabled: false }
            ],
            [
              { code: "clear", name: "清空单元格", disabled: false },
              { code: "all-clear", name: "清空整列", disabled: false },
              { code: "all-clear-line", name: "清空整行", disabled: false },
              { code: "all-clear-table", name: "清空整表", disabled: false }
            ],
            [
              { code: "input", name: "录入等级", disabled: false },
              { code: "save", name: "保存数据", disabled: false },
              { code: "next", name: "下一个", disabled: false },
              { code: "print", name: "打印", disabled: false }
            ]
          ]
        },
        visibleMethod: this.visibleMethod
      },
      groupTitle: null,
      footerData1: [["合计", "", ""]],
      group: [],
      isModify: false,
      dialogVisible: false
    };
  },
  async created() {
    const { data } = await getGroup();
    this.group = data;
  },
  methods: {
    async allSave() {
      for (let i = 1; i < this.formData.number; i++) {
        this.setGradeForTable(this, {}, this.formData.grade);
        await this.saveHandle();
        this.isModify = false;
        this.nextHandle();
      }
      this.dialogVisible = false;
      // 最后一次
      this.setGradeForTable(this, {}, this.formData.grade);
      await this.saveHandle();
      this.$set(this, "formData", {
        grade: "",
        number: 1
      });
      this.isModify = false;
    },
    nextHandle() {
      if (this.isModify) {
        this.$confirm("下一个操作将丢失现在的数据，是否继续", "友情提示", {
          confirmButtonText: "是",
          cancelButtonText: "否",
          type: "warning"
        })
          .then(() => {
            this.showTable = false;
            this.initData();
            this.showTable = true;
            this.$notify.close();
          })
          .catch(() => { });
      } else {
        this.showTable = false;
        this.initData();
        this.showTable = true;
        this.$notify.close();
      }
    },
    async cmdSetGrade(command) {
      let content = {
        "all-table-optimal": "优",
        "all-table-good": "良",
        "all-table-in": "中",
        "all-table-poor": "差"
      };
      this.setGradeForTable(this, {}, content[command]);
      await this.saveHandle();
    },
    exportDataEvent() {
      this.$refs.xTable.exportData({
        filename: "民主测评表",
        sheetName: "Sheet1",
        type: "xlsx"
      });
    },
    findReset() {
      this.$set(this, "searchForm", { group_id: null });
      this.showTable = false;
    },
    // 初始化基础数据
    initBaseData() {
      this.$set(this, "tableData", []);
      this.$set(this, "person", []);
      this.$set(this, "content", []);
      this.$set(this, "listData", []);
    },
    async find() {
      this.showTable = false;
      this.$nextTick(async () => {
        const { data } = await getContent(this.searchForm);
        this.content = data;
        let res = await getPerson(this.searchForm);
        this.person = res.data;
        this.group_id = this.searchForm.group_id;
        this.handleMergeData();
        this.initData();
        this.showTable = true;
        res = await getMark(this.searchForm);
        this.listData = res.data;
      });
    },
    footerMethod1() {
      let len = this.person.length;
      if (this.footerData1[0].length === 3) {
        this.person.forEach(v => {
          this.footerData1[0].push(0);
        });
      }
      this.person.forEach((v, index) => {
        let res = 0;
        let key = "name" + v.order;
        let grade = this.tableData[0][key];
        let title = "德";
        let tmp = this.getScore(title, grade);
        res += tmp;
        grade = this.tableData[4][key];
        title = "能";
        tmp = this.getScore(title, grade);
        res += tmp;
        grade = this.tableData[8][key];
        title = "勤";
        tmp = this.getScore(title, grade);
        res += tmp;
        grade = this.tableData[12][key];
        title = "绩";
        tmp = this.getScore(title, grade);
        res += tmp;
        grade = this.tableData[16][key];
        title = "廉";
        tmp = this.getScore(title, grade);
        res += tmp;
        this.$set(this.footerData1[0], 3 + index, res);
      });
      return this.footerData1;
    },
    setTitle(page = null) {
      if (page) {
        this.groupTitle = "被考核人姓名以及得分" + "【第" + page + "张】";
      } else {
        this.groupTitle = "被考核人姓名以及得分" + "【新表】";
      }
    },
    async getData(row, column, cell, event) {
      const res = await getContentByMark({
        mark: row.mark,
        group_id: this.group_id
      });
      this.mark = row.mark;
      let result = res.data;
      this.setTitle(this.mark);
      let tmp = JSON.parse(JSON.stringify(this.content));
      let i = 0;
      result.forEach(v1 => {
        this.person.forEach(v => {
          tmp[i][`name${v.order}`] = v1[`name${v.order}`];
        });
        i += 4;
      });
      this.tableData = JSON.parse(JSON.stringify(tmp));
      this.$nextTick(() => {
        const $table = this.$refs.xTable;
        $table.setMergeCells(this.mergeCells);
        this.drawer = false;
      });
    },

    // 根据标题和等级获得分数
    getScore(title, grade) {
      const item = this.content.find(
        v => v.content_title === title && v.grade_name === grade
      );
      if (item) {
        return parseFloat(item.grade_value);
      } else {
        return null;
      }
    },
    cellClassName({ row, rowIndex, column, columnIndex }) {},
    handleMergeData() {
      // 根据返回来的人员多少合并单元格
      this.person.forEach((v, index) => {
        const temp = [
          { row: 0, col: 3 + index, rowspan: 4, colspan: 1 },
          { row: 4, col: 3 + index, rowspan: 4, colspan: 1 },
          { row: 8, col: 3 + index, rowspan: 4, colspan: 1 },
          { row: 12, col: 3 + index, rowspan: 4, colspan: 1 },
          { row: 16, col: 3 + index, rowspan: 4, colspan: 1 }
        ];
        this.mergeCells = [...this.mergeCells, ...temp];
      });
    },
    initData() {
      this.content.forEach(v => {
        v.group_id = this.group_id;
        this.person.forEach(v1 => {
          v["name" + v1.order] = null;
        });
      });
      this.tableData = JSON.parse(JSON.stringify(this.content));
      this.$nextTick(() => {
        const $table = this.$refs.xTable;
        $table.setMergeCells(this.mergeCells);
      });
      this.setTitle();
      this.mark = null;
    },

    visibleMethod({ options, column }) {
      const isDisabled = !column;
      options.forEach(list => {
        list.forEach(item => {
          item.disabled = isDisabled;
        });
      });
      return true;
    },
    inputGrade({
      row,
      rowIndex,
      $rowIndex,
      column,
      columnIndex,
      $columnIndex,
      $event
    }) {
      if ($columnIndex >= 3) {
        this.$prompt("请输入简便等级（y=>优、l=>良、z=>中、差=>c）", "提示", {
          confirmButtonText: "确定",
          cancelButtonText: "取消",
          inputPattern: /[ylzcYLZC]{5}/,
          inputErrorMessage:
            "你输入的简便标识不对,数量必须输入5个，每个标识都是从第一项开始"
        })
          .then(({ value }) => {
            for (let i = 0; i < value.length; i++) {
              const result = this.getGradeByInput(value[i]);
              this.tableData[i * 4][`${column.field}`] = result;
            }
            this.$nextTick(() => {
              this.$refs.xTable.updateFooter();
            });
            this.isModify = true;
          })
          .catch(() => {
            this.$message({
              type: "info",
              message: "取消输入"
            });
          });
      }
    },
    getGradeByInput(inputVlaue) {
      let res = "";
      switch (inputVlaue) {
        case "y":
        case "Y":
          res = "优";
          break;
        case "l":
        case "L":
          res = "良";
          break;
        case "z":
        case "Z":
          res = "中";
          break;
        default:
          res = "差";
          break;
      }
      return res;
    },
    isComplete(data) {
      // 查看测评表中是否都打了等级
      const arr = Object.keys(data);
      let bool = true;
      for (let i = 0; i < arr.length; i++) {
        if (data[arr[i]] === null) {
          bool = false;
          break;
        }
      }
      return bool;
    },
    async saveHandle() {
      let grade = [];
      let value = [];
      const res = JSON.parse(JSON.stringify(this.tableData));
      let isComplete = true;
      for (let i = 0; i < res.length; i = i + 4) {
        const title = res[i]["content_title"];
        delete res[i]["id"];
        delete res[i]["content_title"];
        delete res[i]["grade_name"];
        delete res[i]["grade_value"];
        delete res[i]["_X_ROW_KEY"];
        if (this.isComplete(res[i])) {
          grade.push(res[i]);
        } else {
          isComplete = false;
        }
        let tmp = JSON.parse(JSON.stringify(res[i]));
        for (let i = 1; i <= 20; i++) {
          let key = "name" + i;
          let grade = tmp[key];
          tmp[key] = this.getScore(title, grade);
        }
        value.push(tmp);
      }
      if (!isComplete) {
        this.$message.error("测评信息不完整,请注意检查");
        return false;
      }
      const result = {
        grade: JSON.stringify(grade),
        value: JSON.stringify(value),
        mark: this.mark
      };
      const { data } = await store(result);
      this.mark = data.mark;
      this.setTitle(this.mark);
      this.isModify = false;
      const tmp = await getMark(this.searchForm);
      this.$set(this, "listData", tmp.data);
      this.$message.success(`保存成第${this.mark}张测评表`);
    },
    setGradeForTable(obj, menu, grade = null) {
      obj.person.forEach(v1 => {
        obj.tableData.forEach((v, index) => {
          const arr = [0, 4, 8, 12, 16];
          if (arr.includes(index)) {
            const field = `name${v1.order}`;
            v[field] = grade;
          }
        });
      });
      obj.isModify = true;
    },
    async contextMenuClickEvent({ menu, row, column }) {
      const $table = this.$refs.xTable;
      let tmp = null;
      switch (menu.code) {
        case "showTable":
          this.drawer = true;
          break;
        case "hideColumn":
          $table.hideColumn(column);
          break;
        case "showAllColumn":
          $table.resetColumn();
          break;
        case "clear":
          row[`${column.field}`] = null;
          break;
        case "all-clear":
          setAll(this, null);
          break;
        case "all-clear-table":
          this.setGradeForTable(this, menu);
          break;
        case "all-clear-line":
          setGradeForLine(this, menu);
          break;
        case "all-optimal":
          setAll(this, "优");
          break;
        case "all-good":
          setAll(this, "良");
          break;
        case "all-in":
          setAll(this, "中");
          break;
        case "all-poor":
          setAll(this, "差");
          break;
        case "all-table-optimal":
        case "all-table-good":
        case "all-table-in":
        case "all-table-poor":
          this.setGradeForTable(this, menu, menu.name.replace("全", ""));
          break;
        case "all-line-optimal":
        case "all-line-good":
        case "all-line-in":
        case "all-line-poor":
          setGradeForLine(this, menu, menu.name.replace("全", ""));
          break;
        case "optimal":
          setGrade(row, "优");
          break;
        case "good":
          setGrade(row, "良");
          break;
        case "in":
          setGrade(row, "中");
          break;
        case "poor":
          setGrade(row, "差");
          break;
        case "print":
          this.$refs.xTable.print();
          break;
        case "next":
          this.nextHandle();
          break;
        case "save":
          await this.saveHandle();
          break;
        case "input":
          this.$prompt("请输入简便等级（y=>优、l=>良、z=>中、差=>c）", "提示", {
            confirmButtonText: "确定",
            cancelButtonText: "取消",
            inputPattern: /[ylzcYLZC]{5}/,
            inputErrorMessage:
              "你输入的简便标识不对,数量必须输入5个，每个标识都是从第一项开始"
          })
            .then(({ value }) => {
              for (let i = 0; i < value.length; i++) {
                const result = this.getGradeByInput(value[i]);
                this.tableData[i * 4][`${column.field}`] = result;
              }
              this.isModify = true;
            })
            .catch(() => {
              this.$message({
                type: "info",
                message: "取消输入"
              });
            });
          break;
      }
      this.$refs.xTable.updateFooter();
      function setAll(obj, value) {
        obj.tableData.forEach((v, index) => {
          const arr = [0, 4, 8, 12, 16];
          if (arr.includes(index)) {
            v[`${column.field}`] = value;
          }
        });
        obj.isModify = true;
      }
      function setGrade(obj, value) {
        obj[`${column.field}`] = value;
        obj.isModify = true;
      }
      function setGradeForLine(obj, menu, grade = null) {
        const i = obj.tableData.findIndex(v => v.id === row.id);
        const arr = [i];
        obj.person.forEach(v1 => {
          obj.tableData.forEach((v, index) => {
            if (arr.includes(index)) {
              const field = `name${v1.order}`;
              v[field] = grade;
            }
          });
        });
        obj.isModify = true;
      }
    },
    handleClick() {
      this.dialogVisible = true;
    },
    getInsertEvent() {
      const $table = this.$refs.xTable;
      const insertRecords = $table.getInsertRecords();
      VXETable.modal.alert(insertRecords.length);
    },
    getRemoveEvent() {
      const $table = this.$refs.xTable;
      const removeRecords = $table.getRemoveRecords();
      VXETable.modal.alert(removeRecords.length);
    },
    getUpdateEvent() {
      const $table = this.$refs.xTable;
      const updateRecords = $table.getUpdateRecords();
      VXETable.modal.alert(updateRecords.length);
    }
  }
};
</script>

<style scoped>
.mytable-style .vxe-body--row.row-green {
  background-color: #187;
  color: #fff;
}
.mytable-style .vxe-body--column.col-blue {
  background-color: #2db7f5;
  color: #fff;
}
.mytable-style .vxe-body--column.col-red {
  background-color: red;
  color: #fff;
}
.mytable-style .vxe-body--column.col-orange {
  background-color: #f60;
  color: #fff;
}
</style>
