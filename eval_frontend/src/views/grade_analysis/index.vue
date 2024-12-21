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
            v-if="searchForm.group_id"
            @click="exportDataEvent"
            style="margin-left:10px"
            type="primary"
            round
            >导出数据</el-button
          >
          <el-button
            @click="saveHandle"
            style="margin-left:10px"
            type="primary"
            round
            >手工统计</el-button
          >
        </el-form-item>
      </el-form>
    </div>
    <div class="table">
      <vxe-table
        v-if="showTable"
        :menu-config="tableMenu"
        @menu-click="contextMenuClickEvent"
        class="mytable-style"
        border
        resizable
        ref="xTable"
        height="700"
        align="center"
        :print-config="{ isMerge: true }"
        :column-config="{ width: 90 }"
        :data="tableData"
        :footer-method="footerMethod1"
        show-footer
      >
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
  </div>
</template>

<script>
import "@/styles/view.scss";
// import CURD from "@/mixin/simple";
import { getPerson, getGroup } from "@/api/common";
import { index, store } from "@/api/grade_analysis";
import { SearchModel } from "@/model/grade_analysis";
export default {
  name: "GradeIndex",
  // mixins: [CURD],
  data() {
    return {
      module: "grade_analysis",
      newTitle: "新增信息",
      editTitle: "编辑信息",
      group_id: null,
      drawer: false,
      listData: [],
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
        body: {
          options: [[{ code: "print", name: "打印", disabled: false }]]
        },
        visibleMethod: this.visibleMethod
      },
      groupTitle: null,
      footerData1: [["合计", ""]],
      group: []
    };
  },
  async created() {
    const { data } = await getGroup();
    this.group = data;
  },
  methods: {
    async saveHandle() {
      const { info } = await store();
      this.$message.success(info);
    },
    exportDataEvent() {
      this.$refs.xTable.exportData({
        filename: "被考核人得分",
        sheetName: "Sheet1",
        type: "xlsx"
      });
    },
    findReset() {
      this.$set(this, "searchForm", { group_id: null, action: "analysis" });
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
        this.searchForm.action = "analysis";
        const { data } = await index(1, 100, this.searchForm);
        this.content = data;
        let res = await getPerson(this.searchForm);
        this.person = res.data;
        this.group_id = this.searchForm.group_id;
        this.initData();
        this.setTitle();
        this.showTable = true;
      });
    },
    setTitle(page = null) {
      this.groupTitle = "被考核人得分";
    },
    initData() {
      this.content.forEach(v => {
        v.group_id = this.group_id;
        this.person.forEach(v1 => {
          v["name" + v1.order] =
            v["name" + v1.order] !== null
              ? parseFloat(v["name" + v1.order])
              : null;
        });
      });
      this.tableData = JSON.parse(JSON.stringify(this.content));
    },
    async contextMenuClickEvent({ menu, row, column }) {
      const $table = this.$refs.xTable;
      let tmp = null;
      switch (menu.code) {
        case "print":
          this.$refs.xTable.print();
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
      }
      function setGrade(obj, value) {
        obj[`${column.field}`] = value;
      }
    },
    footerMethod1() {
      let len = this.person.length;
      if (this.footerData1[0].length === 2) {
        this.person.forEach(v => {
          this.footerData1[0].push(0);
        });
      }
      this.person.forEach((v, index) => {
        const key = "name" + v.order;
        const value = Number(this.tableData[0][key] + this.tableData[1][key] + this.tableData[2][key] + this.tableData[3][key] + this.tableData[4][key]).toFixed(5);
        this.$set(this.footerData1[0], 2 + index, value);
      });
      console.log(this.footerData1);
      return this.footerData1;
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
